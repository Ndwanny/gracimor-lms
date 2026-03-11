<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

abstract class GracimorTestCase extends TestCase
{
    use RefreshDatabase;

    // ── Seeding ───────────────────────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestingSeeder::class);
    }

    // ── Auth helpers ──────────────────────────────────────────────────────────

    /** Log in as a user by ID and return a Sanctum token header. */
    protected function authAs(int $userId): array
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => $this->emailFor($userId),
            'password' => 'Password1',
        ]);
        $response->assertOk();
        return ['Authorization' => 'Bearer ' . $response->json('token')];
    }

    protected function asOfficer(): array    { return $this->authAs(TestingSeeder::OFFICER_ID); }
    protected function asManager(): array    { return $this->authAs(TestingSeeder::MANAGER_ID); }
    protected function asCeo(): array        { return $this->authAs(TestingSeeder::CEO_ID); }
    protected function asSuperadmin(): array { return $this->authAs(TestingSeeder::SUPERADMIN_ID); }
    protected function asAccountant(): array { return $this->authAs(TestingSeeder::ACCOUNTANT_ID); }

    protected function emailFor(int $userId): string
    {
        return match ($userId) {
            TestingSeeder::OFFICER_ID     => 'officer@test.com',
            TestingSeeder::MANAGER_ID     => 'manager@test.com',
            TestingSeeder::CEO_ID         => 'ceo@test.com',
            TestingSeeder::SUPERADMIN_ID  => 'superadmin@test.com',
            TestingSeeder::ACCOUNTANT_ID  => 'accountant@test.com',
            TestingSeeder::INACTIVE_ID    => 'inactive@test.com',
            default => throw new \InvalidArgumentException("Unknown test user ID: {$userId}"),
        };
    }

    // ── JSON assertion helpers ────────────────────────────────────────────────

    /** Assert the response JSON has a key at a path and the value matches. */
    protected function assertJsonPath(\Illuminate\Testing\TestResponse $response, string $path, mixed $expected): void
    {
        $response->assertJsonPath($path, $expected);
    }

    /** Assert a key is present in the response JSON. */
    protected function assertJsonHasKey(\Illuminate\Testing\TestResponse $response, string $key): void
    {
        $this->assertNotNull(
            data_get($response->json(), $key),
            "Expected JSON key '{$key}' to be present but it was missing or null."
        );
    }

    /** Assert a key is absent from the response JSON. */
    protected function assertJsonMissingKey(\Illuminate\Testing\TestResponse $response, string $key): void
    {
        $this->assertNull(
            data_get($response->json(), $key),
            "Expected JSON key '{$key}' to be absent but it was present."
        );
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// AuthTest
// File: tests/Feature/Auth/AuthTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Auth;
