<?php

namespace Database\Seeders;

use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoanProductSeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = User::first()->id;

        // Gracimor LMS uses a flat tiered rate model.
        // Rate is determined by loan duration ONLY — not by product.
        // TERM_RATES: 1 month = 10%, 2 months = 18%, 3 months = 28%, 4 months = 38%
        // Maximum loan term is 4 months. No other durations exist.

        $products = [

            // ── Product 1: Vehicle-Backed Loan ────────────────────────────────
            [
                'name'                    => 'Vehicle-Backed Loan',
                'code'                    => 'VBL',
                'collateral_type'         => 'vehicle',
                'description'             =>
                    'Short-term loan secured by vehicle logbook or registration document. ' .
                    'Flat tiered rate: 1 month = 10%, 2 months = 18%, 3 months = 28%, 4 months = 38%. ' .
                    'Maximum loan period is 4 months. Equal monthly instalments.',
                'is_active'               => true,
                'default_interest_rate'   => 28.00,   // reference only — actual rate = TERM_RATES[term]
                'min_interest_rate'       => 10.00,
                'max_interest_rate'       => 38.00,
                'interest_method'         => 'flat_rate',
                'min_term_months'         => 1,
                'max_term_months'         => 4,
                'default_term_months'     => 3,
                'min_loan_amount'         => 5000.00,
                'max_loan_amount'         => 200000.00,
                'max_ltv_percent'         => 70.00,
                'processing_fee_fixed'    => 0.00,
                'processing_fee_percent'  => 0.00,
                'penalty_rate_percent'    => 5.00,    // 5% of monthly instalment per overdue month
                'grace_period_days'       => 0,
                'allow_early_settlement'  => true,
                'early_settlement_method' => 'prorated',
                'created_by'              => $createdBy,
            ],

            // ── Product 2: Land-Backed Loan ───────────────────────────────────
            [
                'name'                    => 'Land-Backed Loan',
                'code'                    => 'LBL',
                'collateral_type'         => 'land',
                'description'             =>
                    'Short-term loan secured by title deed or land certificate. ' .
                    'Flat tiered rate: 1 month = 10%, 2 months = 18%, 3 months = 28%, 4 months = 38%. ' .
                    'Maximum loan period is 4 months. Equal monthly instalments.',
                'is_active'               => true,
                'default_interest_rate'   => 28.00,
                'min_interest_rate'       => 10.00,
                'max_interest_rate'       => 38.00,
                'interest_method'         => 'flat_rate',
                'min_term_months'         => 1,
                'max_term_months'         => 4,
                'default_term_months'     => 3,
                'min_loan_amount'         => 5000.00,
                'max_loan_amount'         => 500000.00,
                'max_ltv_percent'         => 60.00,
                'processing_fee_fixed'    => 0.00,
                'processing_fee_percent'  => 0.00,
                'penalty_rate_percent'    => 5.00,
                'grace_period_days'       => 0,
                'allow_early_settlement'  => true,
                'early_settlement_method' => 'prorated',
                'created_by'              => $createdBy,
            ],

        ];

        foreach ($products as $product) {
            LoanProduct::updateOrCreate(
                ['code' => $product['code']],
                $product
            );
        }

        $this->command->info('✓ ' . count($products) . ' loan products seeded (flat-rate, max 4 months).');
    }
}
