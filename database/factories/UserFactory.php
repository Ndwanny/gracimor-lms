<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    use ZambianNames;

    protected $model = User::class;

    public function definition(): array
    {
        $first = $this->zambianFirst();
        $last  = $this->zambianLast();
        $role  = fake()->randomElement(['officer', 'officer', 'officer', 'accountant', 'manager']);

        return [
            'name'              => "{$first} {$last}",
            'email'             => strtolower("{$first}.{$last}" . fake()->randomNumber(2) . '@gracimor.co.zm'),
            'password'          => Hash::make('Password1'),
            'role'              => $role,
            'phone'             => $this->zambianPhone(),
            'is_active'         => true,
            'last_login_at'     => fake()->optional(0.7)->dateTimeBetween('-30 days'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function superadmin(): static
    {
        return $this->state(['role' => 'superadmin', 'email' => 'superadmin@gracimor.co.zm']);
    }

    public function ceo(): static
    {
        return $this->state(['role' => 'ceo']);
    }

    public function manager(): static
    {
        return $this->state(['role' => 'manager']);
    }

    public function officer(): static
    {
        return $this->state(['role' => 'officer']);
    }

    public function accountant(): static
    {
        return $this->state(['role' => 'accountant']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** A user with a known password for feature test login */
    public function withPassword(string $password): static
    {
        return $this->state(['password' => Hash::make($password)]);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// BorrowerFactory
// File: database/factories/BorrowerFactory.php
// ═══════════════════════════════════════════════════════════════════════════════
