<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    // ── Core tiered flat rates (rate covers the FULL term, not annual) ─────────
    private const TERM_RATES = [1 => 10, 2 => 18, 3 => 28, 4 => 38];

    // ── Status distribution ────────────────────────────────────────────────────
    private const LOAN_STATUS_COUNTS = [
        'active'           => 20,
        'overdue'          => 8,
        'pending_approval' => 5,
        'approved'         => 3,
        'closed'           => 10,
        'rejected'         => 3,
        'written_off'      => 1,
    ];

    public function run(): void
    {
        $this->command->info('Seeding development dataset (flat-rate, max 4 months)...');

        DB::transaction(function () {

            // ── 1. Resolve seeded staff accounts ──────────────────────────────
            $officers = User::where('role', 'officer')->pluck('id')->toArray();
            $managers = User::where('role', 'manager')->pluck('id')->toArray();
            $products = LoanProduct::all()->keyBy('code');

            if (empty($officers) || empty($managers)) {
                throw new \RuntimeException('Run UserSeeder before DevelopmentSeeder.');
            }
            if ($products->isEmpty()) {
                throw new \RuntimeException('Run LoanProductSeeder before DevelopmentSeeder.');
            }

            // ── 2. Create borrowers ────────────────────────────────────────────
            $this->command->info('  Creating borrowers...');

            $borrowers = \Database\Factories\BorrowerFactory::new()
                ->count(42)
                ->create([
                    'assigned_officer_id' => fake()->randomElement($officers),
                    'registered_by'       => fake()->randomElement($officers),
                ]);

            $kycPendingBorrowers = \Database\Factories\BorrowerFactory::new()
                ->kycPending()
                ->count(5)
                ->create([
                    'assigned_officer_id' => fake()->randomElement($officers),
                    'registered_by'       => fake()->randomElement($officers),
                ]);

            $unemployedBorrowers = \Database\Factories\BorrowerFactory::new()
                ->state(['employment_status' => 'unemployed', 'employer_name' => null, 'monthly_income' => null])
                ->count(3)
                ->create([
                    'assigned_officer_id' => fake()->randomElement($officers),
                    'registered_by'       => fake()->randomElement($officers),
                ]);

            $allBorrowers = $borrowers->concat($kycPendingBorrowers)->concat($unemployedBorrowers);
            $this->command->info("  ✓ {$allBorrowers->count()} borrowers created.");

            // ── 3. Create collateral assets ────────────────────────────────────
            $this->command->info('  Creating collateral assets...');

            $vehicleAssets = \Database\Factories\CollateralAssetFactory::new()
                ->vehicle()
                ->recentValuation()
                ->count(55)
                ->sequence(fn ($seq) => [
                    'borrower_id' => $allBorrowers->random()->id,
                    'created_by'  => fake()->randomElement($officers),
                ])
                ->create();

            $landAssets = \Database\Factories\CollateralAssetFactory::new()
                ->land()
                ->recentValuation()
                ->count(25)
                ->sequence(fn ($seq) => [
                    'borrower_id' => $allBorrowers->random()->id,
                    'created_by'  => fake()->randomElement($officers),
                ])
                ->create();

            $allAssets = $vehicleAssets->concat($landAssets)->shuffle();
            $this->command->info("  ✓ {$allAssets->count()} collateral assets created.");

            // ── 4. Create loans ────────────────────────────────────────────────
            $this->command->info('  Creating loans...');

            $allLoans  = collect();
            $assetIndex = 0;

            foreach (self::LOAN_STATUS_COUNTS as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $borrower = $allBorrowers->random();
                    $asset    = $allAssets[$assetIndex % $allAssets->count()];
                    $assetIndex++;
                    $officer  = fake()->randomElement($officers);
                    $manager  = fake()->randomElement($managers);
                    $product  = $this->compatibleProduct($products, $asset->asset_type);

                    $loan = $this->createLoan($status, $borrower, $asset, $product, $officer, $manager);

                    if (in_array($status, ['active', 'overdue', 'approved'])) {
                        $asset->update(['status' => 'pledged']);
                    }

                    $allLoans->push($loan);
                }
            }

            $this->command->info("  ✓ {$allLoans->count()} loans created.");

            // ── 5. Generate schedules for disbursed loans ──────────────────────
            $this->command->info('  Generating repayment schedules...');

            $disbursedLoans = $allLoans->whereIn('status', ['active', 'overdue', 'closed', 'written_off']);

            foreach ($disbursedLoans as $loan) {
                $this->generateSchedule($loan);
            }

            $this->command->info("  ✓ Schedules generated for {$disbursedLoans->count()} loans.");

            // ── 6. Record payments ─────────────────────────────────────────────
            $this->command->info('  Recording payment history...');

            $paymentCount = 0;

            // Active: pay all past-dated instalments
            foreach ($allLoans->where('status', 'active') as $loan) {
                $paymentCount += $this->recordPayments($loan, $loan->term_months);
            }

            // Closed: pay all instalments
            foreach ($allLoans->where('status', 'closed') as $loan) {
                $paymentCount += $this->recordPayments($loan, $loan->term_months);
            }

            // Overdue/written_off: pay 0 to (term-1) instalments, then default
            foreach ($allLoans->whereIn('status', ['overdue', 'written_off']) as $loan) {
                $paymentsMade = fake()->numberBetween(0, max(0, $loan->term_months - 1));
                $paymentCount += $this->recordPayments($loan, $paymentsMade);
            }

            $this->command->info("  ✓ {$paymentCount} payment records created.");

            // ── 7. Create penalties for overdue loans ──────────────────────────
            $this->command->info('  Creating penalties for overdue loans...');

            $penaltyCount = 0;
            foreach ($allLoans->whereIn('status', ['overdue', 'written_off']) as $loan) {
                $penaltyCount += $this->createPenalties($loan);
            }

            $this->command->info("  ✓ {$penaltyCount} penalty records created.");

            // ── 8. Build loan balances ─────────────────────────────────────────
            $this->command->info('  Calculating loan balances...');

            foreach ($disbursedLoans as $loan) {
                $this->buildLoanBalance($loan);
            }

            $this->command->info("  ✓ Loan balances calculated.");

            // ── 9. Seed documents for all borrowers ────────────────────────────
            $this->command->info('  Seeding borrower documents...');

            $this->seedDocuments($allBorrowers, $officers[0]);

            $this->command->info("  ✓ Documents assigned to {$allBorrowers->count()} borrowers.");

            // ── 10. Status history ─────────────────────────────────────────────
            $this->command->info('  Creating status history...');

            $this->seedStatusHistory($allLoans, $managers);

            $this->command->info("  ✓ Status history seeded.");

        }); // end DB::transaction

        $this->command->info('✓ Development dataset seeded successfully.');
        $this->command->table(
            ['Status', 'Count'],
            collect(self::LOAN_STATUS_COUNTS)->map(fn ($c, $s) => [$s, $c])->toArray()
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function compatibleProduct($products, string $assetType): LoanProduct
    {
        $compatible = $products->filter(
            fn ($p) => $p->collateral_type === $assetType || $p->collateral_type === 'both'
        );
        return $compatible->isNotEmpty() ? $compatible->random() : $products->first();
    }

    private function createLoan(
        string $status,
        Borrower $borrower,
        CollateralAsset $asset,
        LoanProduct $product,
        int $officerId,
        int $managerId
    ): Loan {
        static $seq = 1;

        // Only 1–4 month terms; rate determined by term (tiered flat rate)
        $term      = fake()->randomElement([1, 2, 3, 4]);
        $rate      = self::TERM_RATES[$term];
        $principal = $this->randomPrincipal($product);

        // Flat-rate formula: interest = principal × rate%  (rate covers the whole term)
        $totalInterest  = round($principal * ($rate / 100), 2);
        $totalRepayable = $principal + $totalInterest;
        $instalment     = round($totalRepayable / $term, 2);

        $now = Carbon::now();

        // Disbursement anchoring — ensures schedule rows are correctly past/pending
        $disbursedAt = match ($status) {
            // active: some instalments past, some pending
            'active'       => $now->copy()->subDays(fake()->numberBetween(5, max(5, $term * 30 - 5))),
            // overdue: all instalments past the due date
            'overdue'      => $now->copy()->subDays($term * 30 + fake()->numberBetween(15, 60)),
            // closed: fully repaid, all instalments past
            'closed'       => $now->copy()->subDays($term * 30 + fake()->numberBetween(5, 30)),
            // written_off: long overdue
            'written_off'  => $now->copy()->subDays($term * 30 + fake()->numberBetween(60, 120)),
            default        => null,
        };

        $approvedAt = $disbursedAt
            ? $disbursedAt->copy()->subDays(fake()->numberBetween(1, 5))
            : null;

        $firstDue  = $disbursedAt ? $disbursedAt->copy()->addMonth() : null;
        $maturity  = $firstDue   ? $firstDue->copy()->addMonths($term - 1) : null;

        $year       = $disbursedAt ? $disbursedAt->year : $now->year;
        $loanNumber = 'LN-' . $year . '-' . str_pad($seq++, 5, '0', STR_PAD_LEFT);

        $procFee = $product->processing_fee_percent
            ? round($principal * ($product->processing_fee_percent / 100), 2)
            : ($product->processing_fee_fixed ?? 0);

        return Loan::create([
            'loan_number'            => $loanNumber,
            'borrower_id'            => $borrower->id,
            'loan_product_id'        => $product->id,
            'collateral_asset_id'    => $asset->id,
            'principal_amount'       => $principal,
            'interest_rate'          => $rate,
            'interest_method'        => 'flat_rate',
            'term_months'            => $term,
            'monthly_instalment'     => $instalment,
            'total_interest'         => $totalInterest,
            'total_repayable'        => $totalRepayable,
            'processing_fee'         => $procFee,
            'ltv_at_origination'     => round(($principal / $asset->estimated_value) * 100, 2),
            'disbursement_method'    => fake()->randomElement(['cash', 'bank_transfer', 'mobile_money']),
            'disbursement_reference' => fake()->optional(0.6)->numerify('TXN############'),
            'first_repayment_date'   => $firstDue?->format('Y-m-d'),
            'maturity_date'          => $maturity?->format('Y-m-d'),
            'disbursed_at'           => $disbursedAt?->format('Y-m-d'),
            'status'                 => $status,
            'applied_by'             => $officerId,
            'approved_by'            => in_array($status, ['active', 'overdue', 'closed', 'written_off', 'approved'])
                ? $managerId : null,
            'approved_at'            => $approvedAt,
            'disbursed_by'           => in_array($status, ['active', 'overdue', 'closed', 'written_off'])
                ? $managerId : null,
            'loan_purpose'           => fake()->optional(0.7)->randomElement([
                'Vehicle purchase', 'Business working capital', 'School fees',
                'Medical expenses', 'Home renovation', 'Agricultural inputs', 'Personal use',
            ]),
            'is_early_settled'       => false,
            'rejection_reason'       => $status === 'rejected'
                ? fake()->randomElement([
                    'Insufficient collateral value',
                    'Existing outstanding loan',
                    'Incomplete documentation',
                    'Credit history concerns',
                ])
                : null,
            'rejected_at' => $status === 'rejected'
                ? $now->copy()->subDays(fake()->numberBetween(5, 60))
                : null,
        ]);
    }

    private function generateSchedule(Loan $loan): void
    {
        $term      = $loan->term_months;
        $principal = $loan->principal_amount;
        $rate      = self::TERM_RATES[$term] ?? 28;

        // Flat rate: interest is fixed, split equally across instalments
        $totalInterest = round($principal * ($rate / 100), 2);
        $basePrincipal = round($principal / $term, 2);
        $baseInterest  = round($totalInterest / $term, 2);

        $firstDue      = Carbon::parse($loan->first_repayment_date);
        $runningBalance = $principal;

        $rows = [];
        for ($n = 1; $n <= $term; $n++) {
            $dueDate = $firstDue->copy()->addMonths($n - 1);
            $isPast  = $dueDate->isPast();

            // Last instalment absorbs any rounding difference
            if ($n === $term) {
                $principalPortion = round($principal - ($basePrincipal * ($term - 1)), 2);
                $interestPortion  = round($totalInterest - ($baseInterest * ($term - 1)), 2);
            } else {
                $principalPortion = $basePrincipal;
                $interestPortion  = $baseInterest;
            }

            $openingBalance  = round($runningBalance, 2);
            $runningBalance -= $principalPortion;
            $closingBalance  = round(max(0, $runningBalance), 2);
            $totalDue        = round($principalPortion + $interestPortion, 2);

            // Status: recordPayments() will mark paid rows; here only set 'overdue' for overdue loans
            $rowStatus = (in_array($loan->status, ['overdue', 'written_off']) && $isPast)
                ? 'overdue'
                : 'pending';

            $rows[] = [
                'loan_id'           => $loan->id,
                'instalment_number' => $n,
                'due_date'          => $dueDate->format('Y-m-d'),
                'principal_portion' => $principalPortion,
                'interest_portion'  => $interestPortion,
                'total_due'         => $totalDue,
                'opening_balance'   => $openingBalance,
                'closing_balance'   => $closingBalance,
                'amount_paid'       => 0,
                'status'            => $rowStatus,
                'paid_at'           => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        LoanSchedule::insert($rows);
    }

    /**
     * Create payment records for the first $count instalments, marking each schedule row as paid.
     * Future-dated instalments are skipped automatically.
     * Returns the number of payments actually created.
     */
    private function recordPayments(Loan $loan, int $count): int
    {
        static $receiptSeq = 1;

        if ($count <= 0) {
            return 0;
        }

        $schedules = LoanSchedule::where('loan_id', $loan->id)
            ->orderBy('instalment_number')
            ->get();

        $paid = 0;
        foreach ($schedules as $schedule) {
            if ($paid >= $count) {
                break;
            }

            $paymentDate = Carbon::parse($schedule->due_date)
                ->subDays(fake()->numberBetween(0, 3));

            if ($paymentDate->isFuture()) {
                break; // instalments not yet due — stop here
            }

            $method = fake()->randomElement(['cash', 'cash', 'bank_transfer', 'mobile_money']);

            Payment::create([
                'loan_id'           => $loan->id,
                'borrower_id'       => $loan->borrower_id,
                'receipt_number'    => 'RCT-' . date('Y') . '-' . str_pad($receiptSeq++, 5, '0', STR_PAD_LEFT),
                'amount_received'   => $schedule->total_due,
                'towards_principal' => $schedule->principal_portion,
                'towards_interest'  => $schedule->interest_portion,
                'towards_penalty'   => 0,
                'balance_before'    => $schedule->opening_balance,
                'balance_after'     => $schedule->closing_balance,
                'payment_type'      => 'instalment',
                'payment_method'    => $method,
                'payment_reference' => in_array($method, ['bank_transfer', 'mobile_money'])
                    ? fake()->numerify('TXN############') : null,
                'payment_date'      => $paymentDate->format('Y-m-d'),
                'is_reversed'       => false,
                'recorded_by'       => $loan->applied_by,
            ]);

            // Mark schedule row as paid
            $schedule->update([
                'status'     => 'paid',
                'amount_paid' => $schedule->total_due,
                'paid_at'    => $paymentDate->format('Y-m-d'),
                'updated_at' => now(),
            ]);

            $paid++;
        }

        return $paid;
    }

    /**
     * Create one penalty record per overdue instalment.
     * Penalty = 5% of monthly instalment (flat, per overdue instalment).
     */
    private function createPenalties(Loan $loan): int
    {
        $schedules = LoanSchedule::where('loan_id', $loan->id)
            ->where('status', 'overdue')
            ->get();

        $count = 0;
        foreach ($schedules as $schedule) {
            // 5% of the monthly instalment per overdue instalment
            $amount      = round($loan->monthly_instalment * 0.05, 2);
            $daysOverdue = (int) Carbon::parse($schedule->due_date)->diffInDays(now());

            Penalty::create([
                'loan_id'                     => $loan->id,
                'loan_schedule_id'            => $schedule->id,
                'borrower_id'                 => $loan->borrower_id,
                'penalty_amount'              => max(10, $amount),
                'penalty_rate_used'           => 5.00,
                'applied_date'                => now()->subDays(fake()->numberBetween(1, 10))->format('Y-m-d'),
                'days_overdue_at_application' => $daysOverdue,
                'status'                      => 'outstanding',
                'is_system_generated'         => true,
            ]);

            $count++;
        }

        return $count;
    }

    private function buildLoanBalance(Loan $loan): void
    {
        $schedules = LoanSchedule::where('loan_id', $loan->id)->get();

        $paidSchedules   = $schedules->where('status', 'paid');
        $principalPaid   = round($paidSchedules->sum('principal_portion'), 2);
        $interestPaid    = round($paidSchedules->sum('interest_portion'), 2);

        $penaltyOutstanding = (float) Penalty::where('loan_id', $loan->id)
            ->where('status', 'outstanding')
            ->sum('penalty_amount');

        if ($loan->status === 'closed') {
            $principalOutstanding = 0.0;
            $interestOutstanding  = 0.0;
            $penaltyOutstanding   = 0.0;
            $totalOutstanding     = 0.0;
        } else {
            $unpaid               = $schedules->whereNotIn('status', ['paid']);
            $principalOutstanding = round($unpaid->sum('principal_portion'), 2);
            $interestOutstanding  = round($unpaid->sum('interest_portion'), 2);
            $totalOutstanding     = round($principalOutstanding + $interestOutstanding + $penaltyOutstanding, 2);
        }

        $instalPaid    = $paidSchedules->count();
        $instalOverdue = $schedules->whereIn('status', ['overdue', 'partial'])->count();
        $instalTotal   = $schedules->count();

        $lastPayment = Payment::where('loan_id', $loan->id)
            ->where('is_reversed', false)
            ->orderByDesc('payment_date')
            ->first();

        LoanBalance::updateOrCreate(
            ['loan_id' => $loan->id],
            [
                'principal_disbursed'   => $loan->principal_amount,
                'principal_paid'        => $principalPaid,
                'principal_outstanding' => $principalOutstanding,
                'interest_charged'      => $loan->total_interest,
                'interest_paid'         => $interestPaid,
                'interest_outstanding'  => $interestOutstanding,
                'penalty_charged'       => round($penaltyOutstanding, 2),
                'penalty_paid'          => 0,
                'penalty_outstanding'   => round($penaltyOutstanding, 2),
                'total_outstanding'     => $totalOutstanding,
                'instalments_total'     => $instalTotal,
                'instalments_paid'      => $instalPaid,
                'instalments_overdue'   => $instalOverdue,
                'last_payment_at'       => $lastPayment?->payment_date,
                'last_payment_amount'   => $lastPayment?->amount_received,
            ]
        );
    }

    /**
     * Assign the 4 shared test documents to every borrower and set photo_path.
     * All borrowers share the same physical files during development/testing.
     */
    private function seedDocuments($allBorrowers, int $uploadedBy): void
    {
        $docDefs = [
            [
                'document_type' => 'national_id',
                'display_name'  => 'National Registration Card',
                'file_name'     => 'NRC or ID.pdf',
                'mime_type'     => 'application/pdf',
            ],
            [
                'document_type' => 'payslip',
                'display_name'  => 'Latest Payslip',
                'file_name'     => 'payslip.pdf',
                'mime_type'     => 'application/pdf',
            ],
            [
                'document_type' => 'land_title_deed',
                'display_name'  => 'Title Deed / Certificate',
                'file_name'     => 'title deed or certificate.pdf',
                'mime_type'     => 'application/pdf',
            ],
            [
                'document_type' => 'vehicle_logbook',
                'display_name'  => 'Vehicle Logbook / Registration',
                'file_name'     => 'vehicle logbook or registration.pdf',
                'mime_type'     => 'application/pdf',
            ],
        ];

        $rows = [];
        foreach ($allBorrowers as $borrower) {
            foreach ($docDefs as $doc) {
                $rows[] = [
                    'documentable_type' => 'App\\Models\\Borrower',
                    'documentable_id'   => $borrower->id,
                    'document_type'     => $doc['document_type'],
                    'display_name'      => $doc['display_name'],
                    'file_path'         => 'borrowers/documents/' . $doc['file_name'],
                    'file_name'         => $doc['file_name'],
                    'mime_type'         => $doc['mime_type'],
                    'file_size_bytes'   => null,
                    'disk'              => 'local',
                    'is_verified'       => true,
                    'verified_by'       => $uploadedBy,
                    'verified_at'       => now()->subDays(fake()->numberBetween(1, 30)),
                    'uploaded_by'       => $uploadedBy,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            // Set photo path on the borrower record
            $borrower->update(['photo_path' => 'borrowers/documents/borrower photo.jpg']);
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('documents')->insert($chunk);
        }
    }

    private function seedStatusHistory($loans, array $managerIds): void
    {
        $rows = [];

        foreach ($loans as $loan) {
            if (in_array($loan->status, ['active', 'overdue', 'closed', 'written_off'])) {
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => null,
                    'to_status'   => 'pending_approval',
                    'notes'       => 'Application submitted.',
                    'changed_by'  => $loan->applied_by,
                    'created_at'  => Carbon::parse($loan->disbursed_at)->subDays(5),
                ];
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => 'pending_approval',
                    'to_status'   => 'approved',
                    'notes'       => 'Credit assessment passed.',
                    'changed_by'  => $loan->approved_by ?? fake()->randomElement($managerIds),
                    'created_at'  => Carbon::parse($loan->disbursed_at)->subDays(2),
                ];
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => 'approved',
                    'to_status'   => 'active',
                    'notes'       => 'Disbursed. Repayment schedule generated.',
                    'changed_by'  => $loan->disbursed_by ?? fake()->randomElement($managerIds),
                    'created_at'  => $loan->disbursed_at,
                ];
            }

            if ($loan->status === 'overdue') {
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => 'active',
                    'to_status'   => 'overdue',
                    'notes'       => 'Auto-marked overdue — missed instalment.',
                    'changed_by'  => null,
                    'created_at'  => now()->subDays(fake()->numberBetween(5, 30)),
                ];
            }

            if ($loan->status === 'closed') {
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => 'active',
                    'to_status'   => 'closed',
                    'notes'       => 'Loan fully repaid. Balance cleared.',
                    'changed_by'  => null,
                    'created_at'  => now()->subDays(fake()->numberBetween(1, 20)),
                ];
            }

            if ($loan->status === 'written_off') {
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => 'overdue',
                    'to_status'   => 'written_off',
                    'notes'       => 'Written off after extended default. Collateral proceedings initiated.',
                    'changed_by'  => fake()->randomElement($managerIds),
                    'created_at'  => now()->subDays(fake()->numberBetween(10, 30)),
                ];
            }

            if ($loan->status === 'rejected') {
                $rows[] = [
                    'loan_id'     => $loan->id,
                    'from_status' => 'pending_approval',
                    'to_status'   => 'rejected',
                    'notes'       => $loan->rejection_reason,
                    'changed_by'  => fake()->randomElement($managerIds),
                    'created_at'  => $loan->rejected_at ?? now()->subDays(10),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('loan_status_history')->insert($chunk);
        }
    }

    private function randomPrincipal(LoanProduct $product): float
    {
        $min   = max($product->min_loan_amount, 5000);
        $max   = min($product->max_loan_amount, 200000);
        $steps = [5000, 10000, 20000, 30000, 50000, 75000, 100000, 150000, 200000];
        $valid = array_filter($steps, fn ($s) => $s >= $min && $s <= $max);

        return empty($valid) ? $min : (float) $valid[array_rand($valid)];
    }
}
