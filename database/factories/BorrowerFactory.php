<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowerFactory extends Factory
{
    use ZambianNames;

    protected $model = Borrower::class;

    /** Sequence counter to generate unique borrower numbers */
    private static int $seq = 1;

    public function definition(): array
    {
        $first    = $this->zambianFirst();
        $last     = $this->zambianLast();
        $employed = fake()->randomElement(['employed', 'employed', 'self_employed', 'unemployed']);
        $dob      = fake()->dateTimeBetween('-60 years', '-22 years');

        return [
            'borrower_number'     => 'BRW-' . str_pad(self::$seq++, 5, '0', STR_PAD_LEFT),
            'first_name'          => $first,
            'last_name'           => $last,
            'nrc_number'          => $this->zambianNrc(),
            'date_of_birth'       => $dob->format('Y-m-d'),
            'gender'              => fake()->randomElement(['male', 'male', 'female', 'female']),
            'phone_primary'       => $this->zambianPhone(),
            'phone_secondary'     => fake()->optional(0.4)->passthrough($this->zambianPhone()),
            'email'               => fake()->optional(0.55)->safeEmail(),
            'residential_address' => $this->zambianAddress(),
            'city_town'           => static::$zambianTowns[array_rand(static::$zambianTowns)],
            'employment_status'   => $employed,
            'employer_name'       => $employed === 'employed' ? fake()->company() . ' Ltd' : null,
            'job_title'           => $employed === 'employed'
                ? fake()->randomElement(['Accountant', 'Teacher', 'Nurse', 'Engineer',
                                        'Sales Representative', 'Driver', 'Manager', 'Secretary'])
                : null,
            'monthly_income'      => in_array($employed, ['employed', 'self_employed'])
                ? fake()->numberBetween(3000, 25000)
                : null,
            'work_phone'          => fake()->optional(0.3)->passthrough($this->zambianPhone()),
            'work_address'        => $employed === 'employed' ? $this->zambianAddress() : null,
            'kyc_status'          => 'verified',
            'kyc_verified_at'     => fake()->dateTimeBetween('-2 years', '-1 month'),
            'photo_path'          => null,
            'internal_notes'      => fake()->optional(0.2)->sentence(),
            'registered_by'       => User::factory(),
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function kycPending(): static
    {
        return $this->state([
            'kyc_status'      => 'pending',
            'kyc_verified_at' => null,
        ]);
    }

    public function kycFailed(): static
    {
        return $this->state([
            'kyc_status'      => 'failed',
            'kyc_verified_at' => null,
        ]);
    }

    public function employed(): static
    {
        return $this->state([
            'employment_status' => 'employed',
            'employer_name'     => fake()->company() . ' Ltd',
            'monthly_income'    => fake()->numberBetween(5000, 25000),
        ]);
    }

    public function selfEmployed(): static
    {
        return $this->state([
            'employment_status' => 'self_employed',
            'employer_name'     => null,
            'monthly_income'    => fake()->numberBetween(3000, 15000),
        ]);
    }

    public function withEmail(): static
    {
        return $this->state(['email' => fake()->unique()->safeEmail()]);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanProductFactory
// File: database/factories/LoanProductFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
