<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    private static int $seq = 1;

    public function definition(): array
    {
        $amount = fake()->randomElement([3000, 4000, 5000, 6000, 7000, 8000, 10000]);
        $date   = fake()->dateTimeBetween('-6 months', 'now');
        $method = fake()->randomElement(['cash', 'cash', 'bank_transfer', 'mobile_money']);

        return [
            'loan_id'         => Loan::factory()->active(),
            'receipt_number'  => 'RCT-' . date('Y', $date->getTimestamp()) . '-' .
                                  str_pad(self::$seq++, 5, '0', STR_PAD_LEFT),
            'amount'          => $amount,
            'payment_method'  => $method,
            'payment_date'    => $date->format('Y-m-d'),
            'reference'       => in_array($method, ['bank_transfer', 'mobile_money'])
                ? fake()->numerify('TXN###########')
                : null,
            'status'          => 'paid',
            'is_reversal'     => false,
            'reversed_at'     => null,
            'reversal_reason' => null,
            'recorded_by'     => User::factory()->officer(),
            'notes'           => null,
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function reversed(): static
    {
        return $this->state([
            'status'          => 'reversed',
            'is_reversal'     => false,
            'reversed_at'     => fake()->dateTimeBetween('-1 month', 'now'),
            'reversal_reason' => fake()->randomElement([
                'Duplicate entry', 'Wrong loan number', 'Bounced cheque',
                'Data entry error',
            ]),
        ]);
    }

    public function cash(): static
    {
        return $this->state(['payment_method' => 'cash', 'reference' => null]);
    }

    public function bankTransfer(): static
    {
        return $this->state([
            'payment_method' => 'bank_transfer',
            'reference'      => fake()->numerify('TXN###########'),
        ]);
    }

    public function mobileMoney(): static
    {
        return $this->state([
            'payment_method' => 'mobile_money',
            'reference'      => fake()->numerify('MM###########'),
        ]);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// PenaltyFactory
// File: database/factories/PenaltyFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
