<?php

namespace Database\Factories;

use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanProductFactory extends Factory
{
    protected $model = LoanProduct::class;

    public function definition(): array
    {
        $method   = fake()->randomElement(['reducing_balance', 'flat_rate']);
        $colType  = fake()->randomElement(['vehicle', 'land', 'both']);
        $minRate  = fake()->randomFloat(2, 18, 28);
        $defRate  = $minRate + fake()->randomFloat(2, 2, 5);
        $maxRate  = $defRate + fake()->randomFloat(2, 2, 5);
        $minLoan  = fake()->randomElement([5000, 10000, 20000]);
        $maxLoan  = fake()->randomElement([200000, 500000, 1000000]);
        $name     = ucfirst(fake()->word()) . ' ' . ucfirst($colType) . ' Loan';

        return [
            'name'                    => $name,
            'code'                    => strtoupper(fake()->unique()->bothify('??##')),
            'collateral_type'         => $colType,
            'description'             => fake()->sentence(),
            'is_active'               => true,
            'default_interest_rate'   => $defRate,
            'min_interest_rate'       => $minRate,
            'max_interest_rate'       => $maxRate,
            'interest_method'         => $method,
            'min_term_months'         => 3,
            'max_term_months'         => 36,
            'min_loan_amount'         => $minLoan,
            'max_loan_amount'         => $maxLoan,
            'max_ltv_percent'         => fake()->randomElement([60, 70, 75, 80]),
            'processing_fee_fixed'    => null,
            'processing_fee_percent'  => fake()->randomElement([1.5, 2.0, 2.5, 3.0]),
            'penalty_rate_percent'    => fake()->randomElement([1.5, 2.0, 2.5]),
            'grace_period_days'       => fake()->randomElement([3, 5, 7, 10]),
            'allow_early_settlement'  => true,
            'early_settlement_method' => 'prorated',
            'created_by'              => User::factory(),
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function vehicleReducingBalance(): static
    {
        return $this->state([
            'name'              => 'Vehicle Loan — Reducing Balance',
            'code'              => 'VRB01',
            'collateral_type'   => 'vehicle',
            'default_interest_rate' => 28.00,
            'interest_method'   => 'reducing_balance',
            'max_ltv_percent'   => 70,
            'grace_period_days' => 7,
        ]);
    }

    public function landFlatRate(): static
    {
        return $this->state([
            'name'              => 'Land-Backed Loan — Flat Rate',
            'code'              => 'LFR01',
            'collateral_type'   => 'land',
            'default_interest_rate' => 24.00,
            'interest_method'   => 'flat_rate',
            'max_ltv_percent'   => 60,
            'grace_period_days' => 10,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// CollateralAssetFactory
// File: database/factories/CollateralAssetFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
