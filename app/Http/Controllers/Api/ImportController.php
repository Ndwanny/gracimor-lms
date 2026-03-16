<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Models\Guarantor;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Services\BorrowerService;
use App\Services\LoanCalculatorService;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function __construct(
        private readonly BorrowerService      $borrowerService,
        private readonly LoanService          $loanService,
        private readonly LoanCalculatorService $calculator,
    ) {}

    // ── CSV helper ────────────────────────────────────────────────────────

    /**
     * Parse an uploaded CSV into an array of associative rows.
     * Skips rows that are empty or start with '#' (instruction rows).
     */
    private function parseCsv(Request $request, string $field = 'file'): array
    {
        $handle  = fopen($request->file($field)->getRealPath(), 'r');
        $headers = null;
        $rows    = [];

        while (($line = fgetcsv($handle)) !== false) {
            if (empty(array_filter($line))) {
                continue;
            }
            if (isset($line[0]) && str_starts_with(trim((string) $line[0]), '#')) {
                continue;
            }
            if ($headers === null) {
                $headers = array_map('trim', $line);
                continue;
            }
            while (count($line) < count($headers)) {
                $line[] = '';
            }
            $rows[] = array_combine($headers, array_map('trim', $line));
        }

        fclose($handle);
        return $rows;
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function num(mixed $value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }

    /**
     * Normalise a date string to YYYY-MM-DD for safe MySQL storage.
     *
     * Handles the formats most common in African/Zambian business spreadsheets:
     *   DD/MM/YYYY  DD-MM-YYYY  DD.MM.YYYY   (explicit day-first)
     *   YYYY/MM/DD  YYYY-MM-DD               (ISO variants)
     *   DD Mon YYYY  Mon DD, YYYY            (text month)
     *
     * Returns null if the value is blank or unparseable.
     * Throws \InvalidArgumentException with a human-readable message so the
     * caller can surface it as a row-level import error.
     */
    private function parseDate(mixed $value): ?string
    {
        if ($this->blank($value)) {
            return null;
        }

        $raw = trim((string) $value);

        // ── 1. Already ISO: YYYY-MM-DD or YYYY/MM/DD ────────────────────────
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $raw, $m)) {
            $dt = Carbon::createFromDate((int)$m[1], (int)$m[2], (int)$m[3]);
            return $dt->toDateString();
        }

        // ── 2. Day-first: DD/MM/YYYY, DD-MM-YYYY, DD.MM.YYYY ────────────────
        //    We enforce day-first for any slash/dash/dot-separated 3-part date
        //    where the year is the last segment (4 digits).
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $raw, $m)) {
            $dt = Carbon::createFromDate((int)$m[3], (int)$m[2], (int)$m[1]);
            return $dt->toDateString();
        }

        // ── 3. Short year: DD/MM/YY, DD-MM-YY ───────────────────────────────
        if (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{2})$/', $raw, $m)) {
            $year = (int)$m[3] >= 50 ? 1900 + (int)$m[3] : 2000 + (int)$m[3];
            $dt   = Carbon::createFromDate($year, (int)$m[2], (int)$m[1]);
            return $dt->toDateString();
        }

        // ── 4. Text month: "15 Mar 2025", "15 March 2025", "Mar 15, 2025" ───
        try {
            $dt = Carbon::parse($raw);
            // Carbon::parse() succeeds even for integers (interprets as timestamp).
            // Guard against that — a plain integer is not a date string.
            if (is_numeric($raw)) {
                throw new \InvalidArgumentException("'{$raw}' looks like a number, not a date");
            }
            return $dt->toDateString();
        } catch (\Exception) {
            throw new \InvalidArgumentException("Cannot parse date '{$raw}' — use YYYY-MM-DD or DD/MM/YYYY");
        }
    }

    // ── Borrowers ─────────────────────────────────────────────────────────

    public function importBorrowers(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $rows     = $this->parseCsv($request);
        $user     = Auth::user();
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            if ($this->blank($row['first_name'] ?? null)
                || $this->blank($row['last_name'] ?? null)
                || $this->blank($row['nrc_number'] ?? null)
                || $this->blank($row['phone_primary'] ?? null)
            ) {
                $errors[] = "Row {$rowNum}: Missing required fields (first_name, last_name, nrc_number, phone_primary)";
                continue;
            }

            if (Borrower::where('nrc_number', $row['nrc_number'])->exists()) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($row, $user) {
                    Borrower::create([
                        'borrower_number'     => $this->borrowerService->generateBorrowerNumber(),
                        'first_name'          => $row['first_name'],
                        'last_name'           => $row['last_name'],
                        'nrc_number'          => $row['nrc_number'],
                        'date_of_birth'       => $this->parseDate($row['date_of_birth'] ?? null),
                        'gender'              => !$this->blank($row['gender'] ?? null) ? $row['gender'] : null,
                        'phone_primary'       => $row['phone_primary'],
                        'phone_secondary'     => !$this->blank($row['phone_secondary'] ?? null) ? $row['phone_secondary'] : null,
                        'email'               => !$this->blank($row['email'] ?? null) ? $row['email'] : null,
                        'residential_address' => !$this->blank($row['residential_address'] ?? null) ? $row['residential_address'] : null,
                        'city_town'           => !$this->blank($row['city_town'] ?? null) ? $row['city_town'] : null,
                        'employment_status'   => !$this->blank($row['employment_status'] ?? null) ? $row['employment_status'] : null,
                        'employer_name'       => !$this->blank($row['employer_name'] ?? null) ? $row['employer_name'] : null,
                        'job_title'           => !$this->blank($row['job_title'] ?? null) ? $row['job_title'] : null,
                        'monthly_income'      => !$this->blank($row['monthly_income'] ?? null) ? $this->num($row['monthly_income']) : null,
                        'work_phone'          => !$this->blank($row['work_phone'] ?? null) ? $row['work_phone'] : null,
                        'work_address'        => !$this->blank($row['work_address'] ?? null) ? $row['work_address'] : null,
                        'kyc_status'          => in_array($row['kyc_status'] ?? '', ['pending','verified','rejected']) ? $row['kyc_status'] : 'pending',
                        'internal_notes'      => !$this->blank($row['internal_notes'] ?? null) ? $row['internal_notes'] : null,
                        'nok_name'            => !$this->blank($row['nok_name'] ?? null) ? $row['nok_name'] : null,
                        'nok_nrc'             => !$this->blank($row['nok_nrc'] ?? null) ? $row['nok_nrc'] : null,
                        'nok_phone'           => !$this->blank($row['nok_phone'] ?? null) ? $row['nok_phone'] : null,
                        'nok_email'           => !$this->blank($row['nok_email'] ?? null) ? $row['nok_email'] : null,
                        'nok_address'         => !$this->blank($row['nok_address'] ?? null) ? $row['nok_address'] : null,
                        'nok_relationship'    => !$this->blank($row['nok_relationship'] ?? null) ? $row['nok_relationship'] : null,
                        'registered_by'       => $user->id,
                        'assigned_officer_id' => $user->id,
                    ]);
                });
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$row['nrc_number']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => count($rows),
        ]);
    }

    // ── Loans ─────────────────────────────────────────────────────────────

    public function importLoans(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $rows     = $this->parseCsv($request);
        $user     = Auth::user();
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        $allowedStatuses   = ['draft','pending','pending_approval','approved','active','overdue','closed','defaulted','written_off','rejected'];
        $disbursedStatuses = ['active','overdue','closed','defaulted','written_off'];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            if ($this->blank($row['borrower_nrc'] ?? null)
                || $this->blank($row['loan_product_name'] ?? null)
                || $this->blank($row['principal_amount'] ?? null)
                || $this->blank($row['term_months'] ?? null)
            ) {
                $errors[] = "Row {$rowNum}: Missing required fields (borrower_nrc, loan_product_name, principal_amount, term_months)";
                continue;
            }

            $borrower = Borrower::where('nrc_number', $row['borrower_nrc'])->first();
            if (!$borrower) {
                $errors[] = "Row {$rowNum}: Borrower NRC '{$row['borrower_nrc']}' not found — import borrowers first";
                continue;
            }

            $product = LoanProduct::where('name', $row['loan_product_name'])->first();
            if (!$product) {
                $errors[] = "Row {$rowNum}: Loan product '{$row['loan_product_name']}' not found — check exact spelling";
                continue;
            }

            $status     = in_array($row['status'] ?? '', $allowedStatuses) ? $row['status'] : 'active';
            $termMonths = (int) $row['term_months'];
            $principal  = $this->num($row['principal_amount']);

            try {
                $interestRate   = !$this->blank($row['interest_rate'] ?? null)
                    ? (float) $row['interest_rate']
                    : (float) $product->default_interest_rate;
                $interestMethod = in_array($row['interest_method'] ?? '', ['flat_rate','reducing_balance'])
                    ? $row['interest_method']
                    : $product->interest_method;

                // Use supplied totals or calculate
                if (!$this->blank($row['total_repayable'] ?? null)
                    && !$this->blank($row['total_interest'] ?? null)
                    && !$this->blank($row['monthly_instalment'] ?? null)
                ) {
                    $totalInterest     = $this->num($row['total_interest']);
                    $totalRepayable    = $this->num($row['total_repayable']);
                    $monthlyInstalment = $this->num($row['monthly_instalment']);
                } else {
                    $summary           = $this->calculator->summarise($principal, $interestRate, $termMonths, $interestMethod);
                    $totalInterest     = $summary['total_interest'];
                    $totalRepayable    = $summary['total_repayable'];
                    $monthlyInstalment = $summary['monthly_instalment'];
                }

                DB::transaction(function () use (
                    $row, $borrower, $product, $user, $status, $termMonths,
                    $interestRate, $interestMethod, $principal,
                    $totalInterest, $totalRepayable, $monthlyInstalment,
                    $disbursedStatuses
                ) {
                    $disbursedAtStr     = $this->parseDate($row['disbursed_at'] ?? null);
                    $disbursedAt        = $disbursedAtStr ? Carbon::parse($disbursedAtStr) : null;

                    $firstRepayStr      = $this->parseDate($row['first_repayment_date'] ?? null);
                    $firstRepaymentDate = $firstRepayStr
                        ? Carbon::parse($firstRepayStr)
                        : ($disbursedAt ? $disbursedAt->copy()->addMonth() : null);

                    $maturityStr        = $this->parseDate($row['maturity_date'] ?? null);
                    $maturityDate       = $maturityStr
                        ? Carbon::parse($maturityStr)
                        : ($firstRepaymentDate ? $firstRepaymentDate->copy()->addMonths($termMonths - 1) : null);

                    $isApproved   = in_array($status, ['approved','active','overdue','closed','defaulted','written_off']);
                    $isDisbursed  = in_array($status, $disbursedStatuses);

                    $loanNumber = !$this->blank($row['loan_number'] ?? null)
                        ? $row['loan_number']
                        : $this->loanService->generateLoanNumber();

                    $loan = Loan::create([
                        'loan_number'            => $loanNumber,
                        'borrower_id'            => $borrower->id,
                        'loan_product_id'        => $product->id,
                        'principal_amount'        => $principal,
                        'interest_rate'           => $interestRate,
                        'interest_method'         => $interestMethod,
                        'term_months'             => $termMonths,
                        'total_interest'          => $totalInterest,
                        'total_repayable'         => $totalRepayable,
                        'monthly_instalment'      => $monthlyInstalment,
                        'processing_fee'          => !$this->blank($row['processing_fee'] ?? null)
                            ? $this->num($row['processing_fee'])
                            : $product->calculateProcessingFee($principal),
                        'status'                  => $status,
                        'first_repayment_date'    => $firstRepaymentDate,
                        'disbursed_at'            => $disbursedAt,
                        'maturity_date'           => $maturityDate,
                        'disbursement_method'     => !$this->blank($row['disbursement_method'] ?? null) ? $row['disbursement_method'] : null,
                        'disbursement_reference'  => !$this->blank($row['disbursement_reference'] ?? null) ? $row['disbursement_reference'] : null,
                        'loan_purpose'            => !$this->blank($row['loan_purpose'] ?? null) ? $row['loan_purpose'] : null,
                        'approval_notes'          => !$this->blank($row['approval_notes'] ?? null) ? $row['approval_notes'] : null,
                        'disburse_notes'          => !$this->blank($row['disburse_notes'] ?? null) ? $row['disburse_notes'] : null,
                        'applied_by'              => $user->id,
                        'approved_by'             => $isApproved ? $user->id : null,
                        'approved_at'             => $isApproved ? now() : null,
                        'disbursed_by'            => $isDisbursed ? $user->id : null,
                    ]);

                    // For disbursed loans: build schedule + initialise balance
                    if ($isDisbursed && $firstRepaymentDate) {
                        $this->loanService->generateSchedule($loan, $firstRepaymentDate);
                        $this->createBalance($loan);
                    }
                });

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$row['borrower_nrc']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => count($rows),
        ]);
    }

    // ── Payments ──────────────────────────────────────────────────────────

    public function importPayments(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $rows     = $this->parseCsv($request);
        $user     = Auth::user();
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        $allowedTypes   = ['instalment','partial','early_settlement','penalty','overpayment'];
        $allowedMethods = ['cash','mobile_money','bank_transfer','cheque'];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            if ($this->blank($row['loan_number'] ?? null)
                || $this->blank($row['payment_date'] ?? null)
                || $this->blank($row['amount_received'] ?? null)
            ) {
                $errors[] = "Row {$rowNum}: Missing required fields (loan_number, payment_date, amount_received)";
                continue;
            }

            $loan = Loan::where('loan_number', $row['loan_number'])->first();
            if (!$loan) {
                $errors[] = "Row {$rowNum}: Loan '{$row['loan_number']}' not found";
                continue;
            }

            $balance = LoanBalance::where('loan_id', $loan->id)->first();
            if (!$balance) {
                $errors[] = "Row {$rowNum}: Loan '{$row['loan_number']}' has no balance record — ensure it was imported as an active/disbursed loan";
                continue;
            }

            $amountReceived   = $this->num($row['amount_received']);
            $towardsPrincipal = !$this->blank($row['towards_principal'] ?? null) ? $this->num($row['towards_principal']) : 0.0;
            $towardsInterest  = !$this->blank($row['towards_interest'] ?? null)  ? $this->num($row['towards_interest'])  : 0.0;
            $towardsPenalty   = !$this->blank($row['towards_penalty'] ?? null)   ? $this->num($row['towards_penalty'])   : 0.0;

            // Auto-allocate if no breakdown provided: interest first, then principal
            if ($towardsPrincipal + $towardsInterest + $towardsPenalty == 0) {
                $towardsInterest  = min($amountReceived, (float) $balance->interest_outstanding);
                $towardsPrincipal = min($amountReceived - $towardsInterest, (float) $balance->principal_outstanding);
            }

            try {
                DB::transaction(function () use (
                    $row, $loan, $balance, $user,
                    $amountReceived, $towardsPrincipal, $towardsInterest, $towardsPenalty,
                    $allowedTypes, $allowedMethods
                ) {
                    $balanceBefore = (float) $balance->total_outstanding;
                    $balanceAfter  = max(0.0, $balanceBefore - $amountReceived);

                    // Generate receipt number inside the transaction
                    $last = Payment::withTrashed()->lockForUpdate()->orderByDesc('id')->value('receipt_number');
                    $receiptNum = $last
                        ? 'RCP-' . str_pad((int) substr($last, 4) + 1, 5, '0', STR_PAD_LEFT)
                        : 'RCP-00001';

                    Payment::create([
                        'receipt_number'    => $receiptNum,
                        'loan_id'           => $loan->id,
                        'borrower_id'       => $loan->borrower_id,
                        'amount_received'   => $amountReceived,
                        'towards_principal' => $towardsPrincipal,
                        'towards_interest'  => $towardsInterest,
                        'towards_penalty'   => $towardsPenalty,
                        'overpayment'       => max(0, $amountReceived - $towardsPrincipal - $towardsInterest - $towardsPenalty),
                        'balance_before'    => $balanceBefore,
                        'balance_after'     => $balanceAfter,
                        'payment_type'      => in_array($row['payment_type'] ?? '', $allowedTypes) ? $row['payment_type'] : 'instalment',
                        'payment_method'    => in_array($row['payment_method'] ?? '', $allowedMethods) ? $row['payment_method'] : 'cash',
                        'payment_reference' => !$this->blank($row['payment_reference'] ?? null) ? $row['payment_reference'] : null,
                        'payment_provider'  => !$this->blank($row['payment_provider'] ?? null) ? $row['payment_provider'] : null,
                        'payment_date'      => $this->parseDate($row['payment_date']),
                        'notes'             => !$this->blank($row['notes'] ?? null) ? $row['notes'] : null,
                        'recorded_by'       => $user->id,
                    ]);

                    // Update running loan balance
                    $balance->increment('principal_paid',        $towardsPrincipal);
                    $balance->decrement('principal_outstanding',  $towardsPrincipal);
                    $balance->increment('interest_paid',          $towardsInterest);
                    $balance->decrement('interest_outstanding',   $towardsInterest);
                    if ($towardsPenalty > 0) {
                        $balance->increment('penalty_paid',       $towardsPenalty);
                        $balance->decrement('penalty_outstanding', $towardsPenalty);
                    }
                    $balance->decrement('total_outstanding', $amountReceived);
                    $balance->increment('instalments_paid',  1);
                    $balance->refresh();
                });
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$row['loan_number']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => count($rows),
        ]);
    }

    // ── Collateral Assets ─────────────────────────────────────────────────

    public function importCollateral(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $rows     = $this->parseCsv($request);
        $user     = Auth::user();
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            if ($this->blank($row['borrower_nrc'] ?? null) || $this->blank($row['asset_type'] ?? null)) {
                $errors[] = "Row {$rowNum}: Missing required fields (borrower_nrc, asset_type)";
                continue;
            }

            $borrower = Borrower::where('nrc_number', $row['borrower_nrc'])->first();
            if (!$borrower) {
                $errors[] = "Row {$rowNum}: Borrower NRC '{$row['borrower_nrc']}' not found";
                continue;
            }

            if (!in_array($row['asset_type'], ['vehicle', 'land'])) {
                $errors[] = "Row {$rowNum}: Invalid asset_type '{$row['asset_type']}' — must be 'vehicle' or 'land'";
                continue;
            }

            try {
                DB::transaction(function () use ($row, $borrower, $user) {
                    CollateralAsset::create([
                        'borrower_id'          => $borrower->id,
                        'asset_type'           => $row['asset_type'],
                        'vehicle_registration' => !$this->blank($row['vehicle_registration'] ?? null) ? $row['vehicle_registration'] : null,
                        'vehicle_make'         => !$this->blank($row['vehicle_make'] ?? null) ? $row['vehicle_make'] : null,
                        'vehicle_model'        => !$this->blank($row['vehicle_model'] ?? null) ? $row['vehicle_model'] : null,
                        'vehicle_year'         => !$this->blank($row['vehicle_year'] ?? null) ? (int) $row['vehicle_year'] : null,
                        'vehicle_color'        => !$this->blank($row['vehicle_color'] ?? null) ? $row['vehicle_color'] : null,
                        'engine_number'        => !$this->blank($row['engine_number'] ?? null) ? $row['engine_number'] : null,
                        'chassis_vin'          => !$this->blank($row['chassis_vin'] ?? null) ? $row['chassis_vin'] : null,
                        'insurance_expiry'     => $this->parseDate($row['insurance_expiry'] ?? null),
                        'insurance_company'    => !$this->blank($row['insurance_company'] ?? null) ? $row['insurance_company'] : null,
                        'plot_number'          => !$this->blank($row['plot_number'] ?? null) ? $row['plot_number'] : null,
                        'title_deed_number'    => !$this->blank($row['title_deed_number'] ?? null) ? $row['title_deed_number'] : null,
                        'land_address'         => !$this->blank($row['land_address'] ?? null) ? $row['land_address'] : null,
                        'land_size_sqm'        => !$this->blank($row['land_size_sqm'] ?? null) ? $this->num($row['land_size_sqm']) : null,
                        'land_ownership_type'  => in_array($row['land_ownership_type'] ?? '', ['freehold','leasehold','customary']) ? $row['land_ownership_type'] : null,
                        'land_type'            => !$this->blank($row['land_type'] ?? null) ? $row['land_type'] : null,
                        'gps_latitude'         => !$this->blank($row['gps_latitude'] ?? null) ? (float) $row['gps_latitude'] : null,
                        'gps_longitude'        => !$this->blank($row['gps_longitude'] ?? null) ? (float) $row['gps_longitude'] : null,
                        'estimated_value'      => !$this->blank($row['estimated_value'] ?? null) ? $this->num($row['estimated_value']) : null,
                        'valuation_date'       => $this->parseDate($row['valuation_date'] ?? null),
                        'valuer_name'          => !$this->blank($row['valuer_name'] ?? null) ? $row['valuer_name'] : null,
                        'valuation_firm'       => !$this->blank($row['valuation_firm'] ?? null) ? $row['valuation_firm'] : null,
                        'status'               => in_array($row['status'] ?? '', ['available','pledged','released']) ? $row['status'] : 'available',
                        'created_by'           => $user->id,
                    ]);
                });
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$row['borrower_nrc']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => count($rows),
        ]);
    }

    // ── Guarantors ────────────────────────────────────────────────────────

    public function importGuarantors(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $rows     = $this->parseCsv($request);
        $user     = Auth::user();
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        $allowedEmployment = ['employed','self_employed','business_owner','unemployed','other'];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            if ($this->blank($row['loan_number'] ?? null)
                || $this->blank($row['full_name'] ?? null)
                || $this->blank($row['phone'] ?? null)
            ) {
                $errors[] = "Row {$rowNum}: Missing required fields (loan_number, full_name, phone)";
                continue;
            }

            $loan = Loan::where('loan_number', $row['loan_number'])->first();
            if (!$loan) {
                $errors[] = "Row {$rowNum}: Loan '{$row['loan_number']}' not found";
                continue;
            }

            try {
                DB::transaction(function () use ($row, $loan, $user, $allowedEmployment) {
                    Guarantor::create([
                        'loan_id'           => $loan->id,
                        'borrower_id'       => $loan->borrower_id,
                        'full_name'         => $row['full_name'],
                        'nrc_number'        => !$this->blank($row['nrc_number'] ?? null) ? $row['nrc_number'] : null,
                        'phone'             => $row['phone'],
                        'email'             => !$this->blank($row['email'] ?? null) ? $row['email'] : null,
                        'address'           => !$this->blank($row['address'] ?? null) ? $row['address'] : null,
                        'relationship'      => !$this->blank($row['relationship'] ?? null) ? $row['relationship'] : null,
                        'employment_status' => in_array($row['employment_status'] ?? '', $allowedEmployment) ? $row['employment_status'] : null,
                        'employer_name'     => !$this->blank($row['employer_name'] ?? null) ? $row['employer_name'] : null,
                        'monthly_income'    => !$this->blank($row['monthly_income'] ?? null) ? $this->num($row['monthly_income']) : null,
                        'status'            => in_array($row['status'] ?? '', ['active','released','defaulted']) ? $row['status'] : 'active',
                        'notes'             => !$this->blank($row['notes'] ?? null) ? $row['notes'] : null,
                        'added_by'          => $user->id,
                    ]);
                });
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$row['loan_number']}): " . $e->getMessage();
            }
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => count($rows),
        ]);
    }

    // ── Exports ───────────────────────────────────────────────────────────

    public function exportBorrowers(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'borrower_number','first_name','last_name','nrc_number','date_of_birth',
                'gender','phone_primary','phone_secondary','email','residential_address',
                'city_town','employment_status','employer_name','job_title','monthly_income',
                'kyc_status','nok_name','nok_nrc','nok_phone','nok_email','nok_address','nok_relationship',
            ]);
            Borrower::orderBy('id')->chunk(200, function ($items) use ($out) {
                foreach ($items as $b) {
                    fputcsv($out, [
                        $b->borrower_number, $b->first_name, $b->last_name, $b->nrc_number,
                        $b->date_of_birth, $b->gender, $b->phone_primary, $b->phone_secondary,
                        $b->email, $b->residential_address, $b->city_town, $b->employment_status,
                        $b->employer_name, $b->job_title, $b->monthly_income, $b->kyc_status,
                        $b->nok_name, $b->nok_nrc, $b->nok_phone, $b->nok_email,
                        $b->nok_address, $b->nok_relationship,
                    ]);
                }
            });
            fclose($out);
        }, 'borrowers_export_' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportLoans(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'loan_number','borrower_nrc','borrower_name','loan_product_name',
                'principal_amount','interest_rate','interest_method','term_months',
                'total_interest','total_repayable','monthly_instalment','processing_fee',
                'disbursed_at','first_repayment_date','maturity_date',
                'disbursement_method','disbursement_reference','status','loan_purpose',
            ]);
            Loan::with(['borrower:id,nrc_number,first_name,last_name', 'loanProduct:id,name'])
                ->orderBy('id')
                ->chunk(200, function ($items) use ($out) {
                    foreach ($items as $l) {
                        fputcsv($out, [
                            $l->loan_number,
                            $l->borrower?->nrc_number,
                            trim(($l->borrower?->first_name ?? '') . ' ' . ($l->borrower?->last_name ?? '')),
                            $l->loanProduct?->name,
                            $l->principal_amount, $l->interest_rate, $l->interest_method,
                            $l->term_months, $l->total_interest, $l->total_repayable,
                            $l->monthly_instalment, $l->processing_fee,
                            $l->disbursed_at, $l->first_repayment_date, $l->maturity_date,
                            $l->disbursement_method, $l->disbursement_reference,
                            $l->status, $l->loan_purpose,
                        ]);
                    }
                });
            fclose($out);
        }, 'loans_export_' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPayments(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'receipt_number','loan_number','borrower_nrc','payment_date',
                'amount_received','towards_principal','towards_interest','towards_penalty',
                'payment_type','payment_method','payment_reference','payment_provider','notes',
            ]);
            Payment::with(['loan:id,loan_number', 'borrower:id,nrc_number'])
                ->orderBy('id')
                ->chunk(200, function ($items) use ($out) {
                    foreach ($items as $p) {
                        fputcsv($out, [
                            $p->receipt_number, $p->loan?->loan_number, $p->borrower?->nrc_number,
                            $p->payment_date, $p->amount_received,
                            $p->towards_principal, $p->towards_interest, $p->towards_penalty,
                            $p->payment_type, $p->payment_method,
                            $p->payment_reference, $p->payment_provider, $p->notes,
                        ]);
                    }
                });
            fclose($out);
        }, 'payments_export_' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ── Template download ─────────────────────────────────────────────────

    /**
     * Generate and stream a CSV import template for the given entity type.
     * Templates are generated inline — no file dependency, works on all deployments.
     */
    public function downloadTemplate(string $type): StreamedResponse
    {
        $templates = [
            'borrowers' => [
                'filename' => 'template_borrowers.csv',
                'rows'     => [
                    // Header
                    ['first_name','last_name','nrc_number','date_of_birth','gender',
                     'phone_primary','phone_secondary','email','residential_address','city_town',
                     'employment_status','employer_name','job_title','monthly_income',
                     'work_phone','work_address','kyc_status','internal_notes',
                     'nok_name','nok_nrc','nok_phone','nok_email','nok_address','nok_relationship'],
                    // Instructions row
                    ['#REQUIRED: first_name | last_name | nrc_number | phone_primary',
                     'ALLOWED gender: male/female/other/prefer_not_to_say','','YYYY-MM-DD',
                     '','','','','','',
                     'ALLOWED: employed/self_employed/business_owner/unemployed/other','','','ZMW numbers only',
                     '','','ALLOWED: pending/verified/rejected (default: pending)','',
                     'Next of Kin','','','','',''],
                    // Example row
                    ['John','Banda','123456/78/1','1985-03-15','male',
                     '0977123456','0955987654','john.banda@email.com','Plot 15 Chamba Valley, Lusaka','Lusaka',
                     'employed','Zambia National Commercial Bank','Branch Manager','15000.00',
                     '0211234567','ZANACO House, Cairo Road','verified','',
                     'Mary Banda','234567/89/1','0977654321','mary.banda@email.com','Plot 15 Lusaka','Spouse'],
                ],
            ],
            'loans' => [
                'filename' => 'template_loans.csv',
                'rows'     => [
                    ['borrower_nrc','loan_product_name','principal_amount','interest_rate',
                     'interest_method','term_months','first_repayment_date','disbursed_at',
                     'maturity_date','disbursement_method','disbursement_reference','status',
                     'loan_purpose','processing_fee','total_interest','total_repayable',
                     'monthly_instalment','approval_notes','disburse_notes'],
                    ['#REQUIRED: borrower_nrc | loan_product_name | principal_amount | term_months',
                     'loan_product_name must exactly match product name in system',
                     'ZMW numbers only','override or leave blank (uses product default)',
                     'ALLOWED: flat_rate/reducing_balance','months (1-4)',
                     'YYYY-MM-DD','YYYY-MM-DD','YYYY-MM-DD',
                     'ALLOWED: cash/bank_transfer/mobile_money/cheque','',
                     'ALLOWED: active/closed/overdue/defaulted/written_off/pending/draft/rejected',
                     '','','leave blank to auto-calculate','','','',''],
                    ['123456/78/1','Personal Loan','10000.00','36.00',
                     'flat_rate','12','2025-02-01','2025-01-15',
                     '2026-01-15','bank_transfer','ZANACO-TXN-00123','active',
                     'Purchase of household furniture','500.00','3600.00','13600.00',
                     '1133.33','','Disbursed to ZANACO acc 1234567'],
                ],
            ],
            'payments' => [
                'filename' => 'template_payments.csv',
                'rows'     => [
                    ['loan_number','payment_date','amount_received','towards_principal',
                     'towards_interest','towards_penalty','payment_type','payment_method',
                     'payment_reference','payment_provider','notes'],
                    ['#REQUIRED: loan_number | payment_date | amount_received',
                     'YYYY-MM-DD','ZMW numbers only',
                     'leave blank to auto-allocate','leave blank to auto-allocate','leave blank to auto-allocate',
                     'ALLOWED: instalment/partial/early_settlement/penalty/overpayment',
                     'ALLOWED: cash/mobile_money/bank_transfer/cheque',
                     'TXN ID / cheque number / bank ref','e.g. Airtel Money / Zanaco',''],
                    ['LN-20250001','2025-02-01','1133.33','833.33',
                     '300.00','0.00','instalment','bank_transfer',
                     'ZANACO-PAY-001','ZANACO',''],
                ],
            ],
            'collateral' => [
                'filename' => 'template_collateral_assets.csv',
                'rows'     => [
                    ['borrower_nrc','asset_type',
                     'vehicle_registration','vehicle_make','vehicle_model','vehicle_year',
                     'vehicle_color','engine_number','chassis_vin','insurance_expiry','insurance_company',
                     'plot_number','title_deed_number','land_address','land_size_sqm',
                     'land_ownership_type','land_type','gps_latitude','gps_longitude',
                     'estimated_value','valuation_date','valuer_name','valuation_firm','status'],
                    ['#REQUIRED: borrower_nrc | asset_type','ALLOWED: vehicle/land',
                     'Fill vehicle columns for vehicles only','','','',
                     '','','','YYYY-MM-DD','',
                     'Fill land columns for land only','','','sqm',
                     'ALLOWED: freehold/leasehold/customary','free text e.g. Residential','','',
                     'ZMW numbers only','YYYY-MM-DD','','',
                     'ALLOWED: available/pledged/released'],
                    ['123456/78/1','vehicle',
                     'ZMB 4521C','Toyota','Hilux','2018',
                     'White','4GR-FE-123456','AHTFB8CD702012345','2025-12-31','Jubilee Insurance',
                     '','','','',
                     '','','','',
                     '18500.00','2024-12-01','James Banda','Valuation Plus Ltd','pledged'],
                ],
            ],
            'guarantors' => [
                'filename' => 'template_guarantors.csv',
                'rows'     => [
                    ['loan_number','full_name','nrc_number','phone','email',
                     'address','relationship','employment_status','employer_name',
                     'monthly_income','status','notes'],
                    ['#REQUIRED: loan_number | full_name | phone',
                     '','','','',
                     '','e.g. Brother/Sister/Spouse/Employer',
                     'ALLOWED: employed/self_employed/business_owner/unemployed/other','',
                     'ZMW numbers only','ALLOWED: active/released/defaulted',''],
                    ['LN-20250001','Michael Banda','345678/90/1','0977334455','michael.banda@email.com',
                     'Flat 3, Kabulonga, Lusaka','Brother','employed','Ministry of Finance',
                     '12000.00','active',''],
                ],
            ],
        ];

        if (!array_key_exists($type, $templates)) {
            abort(404, 'Unknown template type');
        }

        $tpl = $templates[$type];

        return response()->streamDownload(function () use ($tpl) {
            $out = fopen('php://output', 'w');
            foreach ($tpl['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $tpl['filename'], ['Content-Type' => 'text/csv']);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Create the initial loan_balances row for a freshly imported active loan.
     * Mirrors LoanService::initialiseBalance() which is private.
     */
    private function createBalance(Loan $loan): LoanBalance
    {
        $scheduleCount = LoanSchedule::where('loan_id', $loan->id)->count();

        return LoanBalance::create([
            'loan_id'               => $loan->id,
            'principal_disbursed'   => $loan->principal_amount,
            'principal_paid'        => 0,
            'principal_outstanding' => $loan->principal_amount,
            'interest_charged'      => $loan->total_interest,
            'interest_paid'         => 0,
            'interest_outstanding'  => $loan->total_interest,
            'penalty_charged'       => 0,
            'penalty_paid'          => 0,
            'penalty_outstanding'   => 0,
            'total_outstanding'     => $loan->total_repayable,
            'instalments_total'     => $scheduleCount,
            'instalments_paid'      => 0,
            'instalments_overdue'   => 0,
        ]);
    }
}
