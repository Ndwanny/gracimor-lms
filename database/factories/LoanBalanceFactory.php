<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanBalanceFactory extends Factory
{
    protected $model = LoanBalance::class;

    public function definition(): array
    {
        $principal = fake()->numberBetween(10000, 150000);
        $interest  = fake()->numberBetween(1000, 20000);
        $penalty   = 0;

        return [
            'loan_id'               => Loan::factory()->active(),
            'principal_balance'     => $principal,
            'interest_balance'      => $interest,
            'penalty_balance'       => $penalty,
            'total_outstanding'     => $principal + $interest + $penalty,
            'total_paid'            => fake()->numberBetween(5000, 50000),
            'days_overdue'          => 0,
            'instalments_overdue'   => 0,
            'instalments_remaining' => fake()->numberBetween(1, 18),
            'daily_penalty_accrual' => 0,
            'last_payment_date'     => fake()->optional(0.8)->dateTimeBetween('-2 months', '-1 day'),
            'last_payment_amount'   => fake()->optional(0.8)->numberBetween(3000, 10000),
            'recalculated_at'       => now()->subHours(fake()->numberBetween(1, 24)),
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function overdue(int $days = 14): static
    {
        return $this->state([
            'days_overdue'        => $days,
            'instalments_overdue' => fake()->numberBetween(1, 3),
            'penalty_balance'     => fake()->numberBetween(500, 5000),
            'daily_penalty_accrual' => fake()->numberBetween(100, 500),
        ]);
    }

    public function fullyPaid(): static
    {
        return $this->state([
            'principal_balance'  => 0,
            'interest_balance'   => 0,
            'penalty_balance'    => 0,
            'total_outstanding'  => 0,
            'days_overdue'       => 0,
            'instalments_remaining' => 0,
        ]);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// PaymentFactory
// File: database/factories/PaymentFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
