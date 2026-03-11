<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestingSeeder extends Seeder
{
    // Expose IDs as class constants so tests can reference them by name
    const OFFICER_ID      = 1;
    const MANAGER_ID      = 2;
    const CEO_ID          = 3;
    const SUPERADMIN_ID   = 4;
    const ACCOUNTANT_ID   = 5;
    const INACTIVE_ID     = 6;

    const PRODUCT_VEHICLE_ID = 1;
    const PRODUCT_LAND_ID    = 2;

    const BORROWER_VERIFIED_ID = 1;
    const BORROWER_PENDING_ID  = 2;
    const BORROWER_NOEMAIL_ID  = 3;

    const ASSET_VEHICLE_PLEDGED_ID   = 1;
    const ASSET_VEHICLE_AVAILABLE_ID = 2;
    const ASSET_LAND_ID              = 3;

    const LOAN_ACTIVE_ID          = 1;
    const LOAN_OVERDUE_ID         = 2;
    const LOAN_PENDING_ID         = 3;
    const LOAN_APPROVED_ID        = 4;
    const LOAN_CLOSED_ID          = 5;
    const LOAN_REJECTED_ID        = 6;

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('loan_status_histories')->truncate();
        DB::table('loan_balances')->truncate();
        DB::table('loan_schedules')->truncate();
        DB::table('payments')->truncate();
        DB::table('penalties')->truncate();
        DB::table('guarantors')->truncate();
        DB::table('loans')->truncate();
        DB::table('collateral_assets')->truncate();
        DB::table('borrowers')->truncate();
        DB::table('loan_products')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── 1. Users ──────────────────────────────────────────────────────────
        $pwd = Hash::make('Password1');

        User::insert([
            ['id' => 1, 'name' => 'Test Officer',     'email' => 'officer@test.com',     'password' => $pwd, 'role' => 'officer',     'phone' => '+260977000001', 'is_active' => true,  'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Test Manager',     'email' => 'manager@test.com',     'password' => $pwd, 'role' => 'manager',     'phone' => '+260977000002', 'is_active' => true,  'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Test CEO',         'email' => 'ceo@test.com',         'password' => $pwd, 'role' => 'ceo',         'phone' => '+260977000003', 'is_active' => true,  'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Test Superadmin',  'email' => 'superadmin@test.com',  'password' => $pwd, 'role' => 'superadmin',  'phone' => '+260977000004', 'is_active' => true,  'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Test Accountant',  'email' => 'accountant@test.com',  'password' => $pwd, 'role' => 'accountant',  'phone' => '+260977000005', 'is_active' => true,  'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Inactive User',    'email' => 'inactive@test.com',    'password' => $pwd, 'role' => 'officer',     'phone' => '+260977000006', 'is_active' => false, 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── 2. Loan Products ──────────────────────────────────────────────────
        LoanProduct::insert([
            [
                'id' => 1, 'name' => 'Tractor Vehicle Loan', 'code' => 'TVL01',
                'collateral_type' => 'vehicle', 'description' => 'Vehicle loan reducing balance.',
                'is_active' => true, 'interest_rate' => 28.00, 'min_interest_rate' => 24.00,
                'max_interest_rate' => 32.00, 'interest_method' => 'reducing_balance',
                'min_term_months' => 3, 'max_term_months' => 24,
                'min_loan_amount' => 10000, 'max_loan_amount' => 500000, 'max_ltv_percent' => 70,
                'processing_fee_flat' => null, 'processing_fee_percent' => 2.00,
                'penalty_rate_percent' => 2.00, 'penalty_basis' => 'instalment',
                'grace_period_days' => 7, 'allow_early_settlement' => true,
                'early_settlement_method' => 'prorated', 'allow_rate_override' => true,
                'require_guarantor' => false,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2, 'name' => 'Land Asset Loan', 'code' => 'LAL01',
                'collateral_type' => 'land', 'description' => 'Land-backed flat rate loan.',
                'is_active' => true, 'interest_rate' => 24.00, 'min_interest_rate' => 20.00,
                'max_interest_rate' => 28.00, 'interest_method' => 'flat_rate',
                'min_term_months' => 6, 'max_term_months' => 36,
                'min_loan_amount' => 50000, 'max_loan_amount' => 2000000, 'max_ltv_percent' => 60,
                'processing_fee_flat' => null, 'processing_fee_percent' => 1.50,
                'penalty_rate_percent' => 1.50, 'penalty_basis' => 'outstanding_balance',
                'grace_period_days' => 10, 'allow_early_settlement' => true,
                'early_settlement_method' => 'prorated', 'allow_rate_override' => true,
                'require_guarantor' => false,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // ── 3. Borrowers ──────────────────────────────────────────────────────
        Borrower::insert([
            [
                'id' => 1, 'borrower_number' => 'BRW-00001',
                'first_name' => 'Mwansa', 'last_name' => 'Chanda',
                'nrc_number' => '123456/78/1', 'date_of_birth' => '1985-06-15',
                'gender' => 'male', 'phone_primary' => '+260977111001',
                'email' => 'mwansa.chanda@test.com',
                'residential_address' => 'Plot 1234, Cairo Road, Lusaka',
                'city_town' => 'Lusaka', 'employment_status' => 'employed',
                'employer_name' => 'Zambia National Bank', 'job_title' => 'Accountant',
                'monthly_income' => 12000,
                'kyc_status' => 'verified', 'kyc_verified_at' => now()->subMonths(6),
                'assigned_officer_id' => 1,
                'created_at' => now()->subYear(), 'updated_at' => now()->subYear(),
            ],
            [
                'id' => 2, 'borrower_number' => 'BRW-00002',
                'first_name' => 'Bupe', 'last_name' => 'Banda',
                'nrc_number' => '234567/89/2', 'date_of_birth' => '1990-03-22',
                'gender' => 'female', 'phone_primary' => '+260977111002',
                'email' => 'bupe.banda@test.com',
                'residential_address' => 'Plot 5678, Great East Road, Lusaka',
                'city_town' => 'Lusaka', 'employment_status' => 'self_employed',
                'employer_name' => null, 'monthly_income' => 8000,
                'kyc_status' => 'pending', 'kyc_verified_at' => null,
                'assigned_officer_id' => 1,
                'created_at' => now()->subMonths(3), 'updated_at' => now()->subMonths(3),
            ],
            [
                'id' => 3, 'borrower_number' => 'BRW-00003',
                'first_name' => 'Kelvin', 'last_name' => 'Tembo',
                'nrc_number' => '345678/90/3', 'date_of_birth' => '1978-11-08',
                'gender' => 'male', 'phone_primary' => '+260977111003',
                'email' => null,   // no email — tests email-sending guards
                'residential_address' => 'Plot 9012, Kafue Road, Lusaka',
                'city_town' => 'Lusaka', 'employment_status' => 'employed',
                'employer_name' => 'Zambia Revenue Authority', 'monthly_income' => 18000,
                'kyc_status' => 'verified', 'kyc_verified_at' => now()->subMonths(8),
                'assigned_officer_id' => 1,
                'created_at' => now()->subMonths(10), 'updated_at' => now()->subMonths(10),
            ],
        ]);

        // ── 4. Collateral Assets ──────────────────────────────────────────────
        CollateralAsset::insert([
            [
                'id' => 1, 'collateral_type' => 'vehicle',
                'asset_description' => '2020 Toyota Hilux (Silver)',
                'registration_number' => 'ABD 1234', 'owner_name' => 'Mwansa Chanda',
                'owner_nrc' => '123456/78/1', 'estimated_value' => 280000,
                'valuation_date' => now()->subMonths(2)->format('Y-m-d'),
                'valuation_source' => 'Galaxy Motors Zambia',
                'make' => 'Toyota', 'model' => 'Hilux', 'year' => 2020, 'colour' => 'Silver',
                'status' => 'pledged',
                'created_at' => now()->subYear(), 'updated_at' => now()->subYear(),
            ],
            [
                'id' => 2, 'collateral_type' => 'vehicle',
                'asset_description' => '2018 Nissan Navara (White)',
                'registration_number' => 'ABC 5678', 'owner_name' => 'Mwansa Chanda',
                'owner_nrc' => '123456/78/1', 'estimated_value' => 190000,
                'valuation_date' => now()->subMonths(3)->format('Y-m-d'),
                'valuation_source' => 'Central Autos',
                'make' => 'Nissan', 'model' => 'Navara', 'year' => 2018, 'colour' => 'White',
                'status' => 'available',
                'created_at' => now()->subMonths(8), 'updated_at' => now()->subMonths(8),
            ],
            [
                'id' => 3, 'collateral_type' => 'land',
                'asset_description' => 'Plot 4567, Ibex Hill, Lusaka',
                'registration_number' => 'PLT004567', 'owner_name' => 'Kelvin Tembo',
                'owner_nrc' => '345678/90/3', 'estimated_value' => 850000,
                'valuation_date' => now()->subMonths(1)->format('Y-m-d'),
                'valuation_source' => 'Knight Frank Zambia',
                'plot_number' => 'Plot 4567', 'location_description' => 'Ibex Hill, Lusaka',
                'title_deed_number' => 'LUS/4567/2019',
                'status' => 'available',
                'created_at' => now()->subMonths(10), 'updated_at' => now()->subMonths(10),
            ],
        ]);

        // ── 5. Loans ──────────────────────────────────────────────────────────
        $disbursed6mAgo  = now()->subMonths(6);
        $disbursed14mAgo = now()->subMonths(14);
        $disbursed18mAgo = now()->subMonths(18);

        Loan::insert([
            // Loan 1 — ACTIVE, K50,000, 12 months
            [
                'id' => 1, 'loan_number' => 'GRS-TEST-00001',
                'borrower_id' => 1, 'loan_product_id' => 1, 'collateral_asset_id' => 1,
                'principal_amount' => 50000, 'interest_rate' => 28.00,
                'interest_method' => 'reducing_balance', 'term_months' => 12,
                'monthly_instalment' => 4907.13,
                'total_interest' => 8885.56, 'total_repayable' => 58885.56,
                'processing_fee' => 1000.00, 'ltv_at_origination' => 17.86,
                'disbursement_method' => 'bank_transfer', 'disbursement_reference' => 'TXN000000001',
                'first_repayment_date' => $disbursed6mAgo->copy()->addMonth()->startOfMonth(),
                'maturity_date' => $disbursed6mAgo->copy()->addMonths(13)->startOfMonth(),
                'disbursed_at' => $disbursed6mAgo, 'status' => 'active',
                'applied_by' => 1, 'approved_by' => 2, 'disbursed_by' => 2,
                'approved_at' => $disbursed6mAgo->copy()->subDays(2),
                'loan_purpose' => 'Business working capital',
                'is_early_settled' => false,
                'created_at' => $disbursed6mAgo->copy()->subDays(5),
                'updated_at' => now(),
            ],
            // Loan 2 — OVERDUE, K30,000, 9 months
            [
                'id' => 2, 'loan_number' => 'GRS-TEST-00002',
                'borrower_id' => 1, 'loan_product_id' => 1, 'collateral_asset_id' => 2,
                'principal_amount' => 30000, 'interest_rate' => 28.00,
                'interest_method' => 'reducing_balance', 'term_months' => 9,
                'monthly_instalment' => 3803.62,
                'total_interest' => 4232.58, 'total_repayable' => 34232.58,
                'processing_fee' => 600.00, 'ltv_at_origination' => 15.79,
                'disbursement_method' => 'cash', 'disbursement_reference' => null,
                'first_repayment_date' => $disbursed14mAgo->copy()->addMonth()->startOfMonth(),
                'maturity_date' => $disbursed14mAgo->copy()->addMonths(10)->startOfMonth(),
                'disbursed_at' => $disbursed14mAgo, 'status' => 'overdue',
                'applied_by' => 1, 'approved_by' => 2, 'disbursed_by' => 2,
                'approved_at' => $disbursed14mAgo->copy()->subDays(3),
                'loan_purpose' => 'Vehicle purchase',
                'is_early_settled' => false,
                'created_at' => $disbursed14mAgo->copy()->subDays(7),
                'updated_at' => now(),
            ],
            // Loan 3 — PENDING APPROVAL
            [
                'id' => 3, 'loan_number' => 'GRS-TEST-00003',
                'borrower_id' => 2, 'loan_product_id' => 1, 'collateral_asset_id' => 2,
                'principal_amount' => 20000, 'interest_rate' => 28.00,
                'interest_method' => 'reducing_balance', 'term_months' => 6,
                'monthly_instalment' => 3750.00,
                'total_interest' => 2500.00, 'total_repayable' => 22500.00,
                'processing_fee' => 400.00, 'ltv_at_origination' => 10.53,
                'disbursement_method' => 'mobile_money', 'disbursement_reference' => null,
                'first_repayment_date' => now()->addMonths(2)->startOfMonth(),
                'maturity_date' => now()->addMonths(8)->startOfMonth(),
                'disbursed_at' => null, 'status' => 'pending_approval',
                'applied_by' => 1, 'approved_by' => null, 'disbursed_by' => null,
                'approved_at' => null, 'loan_purpose' => 'Personal use',
                'is_early_settled' => false,
                'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3),
            ],
            // Loan 4 — APPROVED (awaiting disbursement)
            [
                'id' => 4, 'loan_number' => 'GRS-TEST-00004',
                'borrower_id' => 3, 'loan_product_id' => 2, 'collateral_asset_id' => 3,
                'principal_amount' => 100000, 'interest_rate' => 24.00,
                'interest_method' => 'flat_rate', 'term_months' => 12,
                'monthly_instalment' => 10333.33,
                'total_interest' => 24000.00, 'total_repayable' => 124000.00,
                'processing_fee' => 1500.00, 'ltv_at_origination' => 11.76,
                'disbursement_method' => 'bank_transfer', 'disbursement_reference' => null,
                'first_repayment_date' => now()->addMonth()->startOfMonth(),
                'maturity_date' => now()->addMonths(13)->startOfMonth(),
                'disbursed_at' => null, 'status' => 'approved',
                'applied_by' => 1, 'approved_by' => 2, 'disbursed_by' => null,
                'approved_at' => now()->subDays(1),
                'loan_purpose' => 'Home renovation',
                'is_early_settled' => false,
                'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(1),
            ],
            // Loan 5 — CLOSED (fully repaid)
            [
                'id' => 5, 'loan_number' => 'GRS-TEST-00005',
                'borrower_id' => 1, 'loan_product_id' => 1, 'collateral_asset_id' => 2,
                'principal_amount' => 25000, 'interest_rate' => 28.00,
                'interest_method' => 'reducing_balance', 'term_months' => 6,
                'monthly_instalment' => 4627.00,
                'total_interest' => 2762.00, 'total_repayable' => 27762.00,
                'processing_fee' => 500.00, 'ltv_at_origination' => 13.16,
                'disbursement_method' => 'cash', 'disbursement_reference' => null,
                'first_repayment_date' => $disbursed18mAgo->copy()->addMonth()->startOfMonth(),
                'maturity_date' => $disbursed18mAgo->copy()->addMonths(7)->startOfMonth(),
                'disbursed_at' => $disbursed18mAgo, 'status' => 'closed',
                'applied_by' => 1, 'approved_by' => 2, 'disbursed_by' => 2,
                'approved_at' => $disbursed18mAgo->copy()->subDays(2),
                'loan_purpose' => 'School fees',
                'is_early_settled' => false,
                'created_at' => $disbursed18mAgo->copy()->subDays(4),
                'updated_at' => now()->subMonths(11),
            ],
            // Loan 6 — REJECTED
            [
                'id' => 6, 'loan_number' => 'GRS-TEST-00006',
                'borrower_id' => 2, 'loan_product_id' => 1, 'collateral_asset_id' => 2,
                'principal_amount' => 50000, 'interest_rate' => 28.00,
                'interest_method' => 'reducing_balance', 'term_months' => 12,
                'monthly_instalment' => 4907.13, 'total_interest' => 8885.56,
                'total_repayable' => 58885.56, 'processing_fee' => 1000.00,
                'ltv_at_origination' => 26.32,
                'disbursement_method' => 'bank_transfer', 'disbursement_reference' => null,
                'first_repayment_date' => now()->addMonths(2)->startOfMonth(),
                'maturity_date' => now()->addMonths(14)->startOfMonth(),
                'disbursed_at' => null, 'status' => 'rejected',
                'applied_by' => 1, 'approved_by' => null, 'disbursed_by' => null,
                'approved_at' => null,
                'rejection_reason' => 'KYC not verified',
                'rejected_at' => now()->subDays(2),
                'loan_purpose' => 'Business working capital',
                'is_early_settled' => false,
                'created_at' => now()->subDays(7), 'updated_at' => now()->subDays(2),
            ],
        ]);

        // ── 6. Schedules + payments for loan 1 (active, 6 months done) ────────
        $this->seedActiveLoanScheduleAndPayments();

        // ── 7. Schedule + payments for loan 2 (overdue) ───────────────────────
        $this->seedOverdueLoanScheduleAndPayments();

        // ── 8. Full schedule for loan 5 (closed) ──────────────────────────────
        $this->seedClosedLoanScheduleAndPayments();

        // ── 9. Loan balances ──────────────────────────────────────────────────
        $this->seedLoanBalances();

        $this->command->info('✓ Testing dataset seeded (6 users, 2 products, 3 borrowers, 3 assets, 6 loans).');
    }

    private function seedActiveLoanScheduleAndPayments(): void
    {
        // Loan 1: K50,000 / 28% reducing / 12 months / started 6 months ago
        $disbursedAt = now()->subMonths(6);
        $instalment  = 4907.13;
        $balance     = 50000.00;
        $r           = (28.00 / 100) / 12;

        static $receiptSeq = 1;
        $rows = [];

        for ($n = 1; $n <= 12; $n++) {
            $dueDate      = $disbursedAt->copy()->addMonth()->startOfMonth()->addMonths($n - 1);
            $intComp      = round($balance * $r, 2);
            $prinComp     = round($instalment - $intComp, 2);
            if ($n === 12) { $prinComp = round($balance, 2); }
            $opening      = round($balance, 2);
            $balance      = max(0, $balance - $prinComp);
            $isPaid       = $n <= 5; // first 5 paid
            $status       = $isPaid ? 'paid' : ($n === 6 ? 'pending' : 'pending');

            $rows[] = [
                'loan_id' => 1, 'instalment_number' => $n, 'due_date' => $dueDate->format('Y-m-d'),
                'principal_component' => $prinComp, 'interest_component' => $intComp,
                'principal_paid' => $isPaid ? $prinComp : 0,
                'interest_paid'  => $isPaid ? $intComp  : 0,
                'opening_balance' => $opening, 'closing_balance' => round($balance, 2),
                'status' => $status,
                'paid_at' => $isPaid ? $dueDate->copy()->subDays(1)->format('Y-m-d H:i:s') : null,
            ];
        }

        LoanSchedule::insert($rows);

        // 5 payments
        for ($n = 1; $n <= 5; $n++) {
            $pd = now()->subMonths(6)->addMonth()->startOfMonth()->addMonths($n - 1)->subDay();
            Payment::create([
                'loan_id' => 1, 'receipt_number' => 'RCT-TEST-' . str_pad($receiptSeq++, 5, '0', STR_PAD_LEFT),
                'amount' => $instalment, 'payment_method' => 'bank_transfer',
                'payment_date' => $pd->format('Y-m-d'),
                'reference' => 'TXN' . str_pad($n, 9, '0', STR_PAD_LEFT),
                'status' => 'paid', 'is_reversal' => false, 'recorded_by' => 1,
            ]);
        }
    }

    private function seedOverdueLoanScheduleAndPayments(): void
    {
        // Loan 2: K30,000 / 28% / 9 months / started 14 months ago (9-month loan = matured)
        $disbursedAt = now()->subMonths(14);
        $instalment  = 3803.62;
        $balance     = 30000.00;
        $r           = (28.00 / 100) / 12;

        static $receiptSeq = 100;
        $rows = [];

        for ($n = 1; $n <= 9; $n++) {
            $dueDate  = $disbursedAt->copy()->addMonth()->startOfMonth()->addMonths($n - 1);
            $intComp  = round($balance * $r, 2);
            $prinComp = round($instalment - $intComp, 2);
            if ($n === 9) { $prinComp = round($balance, 2); }
            $opening  = round($balance, 2);
            $balance  = max(0, $balance - $prinComp);
            // First 3 paid, rest overdue
            $isPaid   = $n <= 3;
            $status   = $isPaid ? 'paid' : 'overdue';

            $rows[] = [
                'loan_id' => 2, 'instalment_number' => $n, 'due_date' => $dueDate->format('Y-m-d'),
                'principal_component' => $prinComp, 'interest_component' => $intComp,
                'principal_paid' => $isPaid ? $prinComp : 0,
                'interest_paid'  => $isPaid ? $intComp  : 0,
                'opening_balance' => $opening, 'closing_balance' => round($balance, 2),
                'status' => $status,
                'paid_at' => $isPaid ? $dueDate->copy()->subDays(2)->format('Y-m-d H:i:s') : null,
            ];
        }

        LoanSchedule::insert($rows);

        // 3 payments only
        for ($n = 1; $n <= 3; $n++) {
            $pd = $disbursedAt->copy()->addMonth()->startOfMonth()->addMonths($n - 1)->subDays(2);
            Payment::create([
                'loan_id' => 2, 'receipt_number' => 'RCT-TEST-' . str_pad($receiptSeq++, 5, '0', STR_PAD_LEFT),
                'amount' => $instalment, 'payment_method' => 'cash',
                'payment_date' => $pd->format('Y-m-d'),
                'reference' => null, 'status' => 'paid', 'is_reversal' => false, 'recorded_by' => 1,
            ]);
        }

        // Create penalty for overdue instalments 4-9
        $overdueSchedules = LoanSchedule::where('loan_id', 2)->where('status', 'overdue')->get();
        foreach ($overdueSchedules as $sched) {
            $daysOverdue = max(1, Carbon::parse($sched->due_date)->diffInDays(now()));
            $daysAfterGrace = max(0, $daysOverdue - 7);
            if ($daysAfterGrace > 0) {
                \App\Models\Penalty::create([
                    'loan_id' => 2, 'loan_schedule_id' => $sched->id,
                    'amount' => round(($sched->principal_component + $sched->interest_component) * 0.02, 2),
                    'rate_applied' => 2.00, 'basis' => 'instalment',
                    'days_overdue' => $daysOverdue, 'days_after_grace' => $daysAfterGrace,
                    'status' => 'outstanding', 'applied_at' => now()->subDays(5),
                ]);
            }
        }
    }

    private function seedClosedLoanScheduleAndPayments(): void
    {
        // Loan 5: K25,000 / 6 months — all paid
        $disbursedAt = now()->subMonths(18);
        $instalment  = 4627.00;
        $balance     = 25000.00;
        $r           = (28.00 / 100) / 12;

        static $receiptSeq = 200;
        $rows = [];

        for ($n = 1; $n <= 6; $n++) {
            $dueDate  = $disbursedAt->copy()->addMonth()->startOfMonth()->addMonths($n - 1);
            $intComp  = round($balance * $r, 2);
            $prinComp = round($instalment - $intComp, 2);
            if ($n === 6) { $prinComp = round($balance, 2); }
            $opening  = round($balance, 2);
            $balance  = max(0, $balance - $prinComp);

            $rows[] = [
                'loan_id' => 5, 'instalment_number' => $n, 'due_date' => $dueDate->format('Y-m-d'),
                'principal_component' => $prinComp, 'interest_component' => $intComp,
                'principal_paid' => $prinComp, 'interest_paid' => $intComp,
                'opening_balance' => $opening, 'closing_balance' => round($balance, 2),
                'status' => 'paid',
                'paid_at' => $dueDate->copy()->subDays(1)->format('Y-m-d H:i:s'),
            ];

            Payment::create([
                'loan_id' => 5, 'receipt_number' => 'RCT-TEST-' . str_pad($receiptSeq++, 5, '0', STR_PAD_LEFT),
                'amount' => $instalment, 'payment_method' => 'cash',
                'payment_date' => $dueDate->copy()->subDays(1)->format('Y-m-d'),
                'reference' => null, 'status' => 'paid', 'is_reversal' => false, 'recorded_by' => 1,
            ]);
        }

        LoanSchedule::insert($rows);
    }

    private function seedLoanBalances(): void
    {
        $balances = [
            // Loan 1: active, 5 of 12 paid, roughly K28k remaining
            ['loan_id' => 1, 'principal_balance' => 27842.00, 'interest_balance' => 3200.00,
             'penalty_balance' => 0, 'total_outstanding' => 31042.00, 'total_paid' => 24535.65,
             'days_overdue' => 0, 'instalments_overdue' => 0, 'instalments_remaining' => 7,
             'daily_penalty_accrual' => 0,
             'last_payment_date' => now()->subMonth()->startOfMonth()->subDay()->format('Y-m-d'),
             'last_payment_amount' => 4907.13, 'recalculated_at' => now()],

            // Loan 2: overdue, 3 of 9 paid, 6 overdue
            ['loan_id' => 2, 'principal_balance' => 20841.00, 'interest_balance' => 2400.00,
             'penalty_balance' => 1200.00, 'total_outstanding' => 24441.00, 'total_paid' => 11410.86,
             'days_overdue' => Carbon::parse('first_repayment_date')->diffInDays(now(), false) > 0 ? 30 : 14,
             'instalments_overdue' => 6, 'instalments_remaining' => 6,
             'daily_penalty_accrual' => 16.29,
             'last_payment_date' => now()->subMonths(11)->format('Y-m-d'),
             'last_payment_amount' => 3803.62, 'recalculated_at' => now()],

            // Loan 5: closed, fully paid
            ['loan_id' => 5, 'principal_balance' => 0, 'interest_balance' => 0,
             'penalty_balance' => 0, 'total_outstanding' => 0, 'total_paid' => 27762.00,
             'days_overdue' => 0, 'instalments_overdue' => 0, 'instalments_remaining' => 0,
             'daily_penalty_accrual' => 0,
             'last_payment_date' => now()->subMonths(11)->startOfMonth()->subDay()->format('Y-m-d'),
             'last_payment_amount' => 4627.00, 'recalculated_at' => now()],
        ];

        foreach ($balances as $b) {
            LoanBalance::updateOrCreate(['loan_id' => $b['loan_id']], $b);
        }
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// DatabaseSeeder
// File: database/seeders/DatabaseSeeder.php
//
// Master orchestrator.
//
// Environments:
//   production   → UserSeeder + LoanProductSeeder + SmsTemplateSeeder only
//   staging      → same as production
//   testing      → TestingSeeder only (called via RefreshDatabase trait)
//   local/dev    → all seeders in correct dependency order
//
// Usage:
//   php artisan migrate:fresh --seed                  ← dev (full dataset)
//   php artisan migrate:fresh --seed --env=testing    ← testing dataset only
//   php artisan db:seed --class=DevelopmentSeeder     ← dev data only (users/products already seeded)
// ═══════════════════════════════════════════════════════════════════════════════
