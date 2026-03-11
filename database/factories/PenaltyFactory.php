<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenaltyFactory extends Factory
{
    protected $model = Penalty::class;

    public function definition(): array
    {
        $daysOverdue    = fake()->numberBetween(8, 60);
        $gracePeriod    = 7;
        $daysAfterGrace = max(0, $daysOverdue - $gracePeriod);
        $amount         = round(fake()->numberBetween(500, 5000) * ($daysAfterGrace / 30), 2);

        return [
            'loan_id'          => Loan::factory()->overdue(),
            'loan_schedule_id' => null,
            'amount'           => max(50, $amount),
            'rate_applied'     => fake()->randomElement([1.5, 2.0, 2.5]),
            'basis'            => fake()->randomElement(['instalment', 'outstanding_balance']),
            'days_overdue'     => $daysOverdue,
            'days_after_grace' => $daysAfterGrace,
            'status'           => 'outstanding',
            'applied_at'       => fake()->dateTimeBetween('-' . $daysOverdue . ' days', 'now'),
            'applied_by'       => null,
            'waiver_reason'    => null,
            'waiver_notes'     => null,
            'waived_at'        => null,
            'waived_by'        => null,
            'notes'            => null,
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function outstanding(): static
    {
        return $this->state(['status' => 'outstanding', 'waived_at' => null]);
    }

    public function waived(): static
    {
        return $this->state([
            'status'        => 'waived',
            'waiver_reason' => fake()->randomElement([
                'hardship', 'error', 'goodwill', 'management_decision',
            ]),
            'waiver_notes'  => fake()->optional()->sentence(),
            'waived_at'     => fake()->dateTimeBetween('-1 month', 'now'),
            'waived_by'     => User::factory()->manager(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid']);
    }
}
