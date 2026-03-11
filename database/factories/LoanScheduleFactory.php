<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanScheduleFactory extends Factory
{
    protected $model = LoanSchedule::class;

    public function definition(): array
    {
        $principal  = fake()->numberBetween(3000, 8000);
        $interest   = fake()->numberBetween(500, 2000);
        $dueDate    = fake()->dateTimeBetween('-6 months', '+12 months');

        return [
            'loan_id'            => Loan::factory()->active(),
            'instalment_number'  => 1,
            'due_date'           => $dueDate->format('Y-m-d'),
            'principal_component'=> $principal,
            'interest_component' => $interest,
            'principal_paid'     => 0,
            'interest_paid'      => 0,
            'opening_balance'    => $principal * 5,
            'closing_balance'    => $principal * 4,
            'status'             => 'pending',
            'paid_at'            => null,
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function paid(): static
    {
        return $this->state(function (array $attrs) {
            return [
                'principal_paid' => $attrs['principal_component'],
                'interest_paid'  => $attrs['interest_component'],
                'status'         => 'paid',
                'paid_at'        => fake()->dateTimeBetween('-3 months', '-1 day'),
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date' => fake()->dateTimeBetween('-60 days', '-5 days')->format('Y-m-d'),
            'status'   => 'overdue',
        ]);
    }

    public function partial(): static
    {
        return $this->state(function (array $attrs) {
            return [
                'principal_paid' => round($attrs['principal_component'] * 0.5, 2),
                'interest_paid'  => round($attrs['interest_component'] * 0.5, 2),
                'status'         => 'partial',
            ];
        });
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanBalanceFactory
// File: database/factories/LoanBalanceFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
