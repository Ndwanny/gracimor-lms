<?php

namespace Tests\Feature\Auth;

use App\Models\Borrower;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

class AuthTest extends GracimorTestCase
{
    // ── Login ─────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_login_with_correct_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'officer@test.com',
            'password' => 'Password1',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'role', 'email'],
            ]);

        $this->assertEquals('officer', $response->json('user.role'));
        $this->assertEquals(TestingSeeder::OFFICER_ID, $response->json('user.id'));
        $this->assertNotEmpty($response->json('token'));
    }

    /** @test */
    public function login_fails_with_wrong_password(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'officer@test.com',
            'password' => 'WrongPassword',
        ])->assertUnauthorized()
          ->assertJsonFragment(['message' => 'Invalid credentials.']);
    }

    /** @test */
    public function login_fails_for_nonexistent_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@test.com',
            'password' => 'Password1',
        ])->assertUnauthorized();
    }

    /** @test */
    public function login_fails_for_inactive_user(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'inactive@test.com',
            'password' => 'Password1',
        ])->assertForbidden()
          ->assertJsonFragment(['message' => 'Your account is inactive.']);
    }

    /** @test */
    public function login_validates_required_fields(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function login_validates_email_format(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'Password1',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['email']);
    }

    // ── Authenticated access ──────────────────────────────────────────────────

    /** @test */
    public function authenticated_user_can_access_protected_routes(): void
    {
        $this->getJson('/api/loans', $this->asOfficer())
            ->assertOk();
    }

    /** @test */
    public function unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/loans')
            ->assertUnauthorized();
    }

    /** @test */
    public function invalid_token_is_rejected(): void
    {
        $this->getJson('/api/loans', [
            'Authorization' => 'Bearer totally-fake-token',
        ])->assertUnauthorized();
    }

    // ── Profile ───────────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_user_can_fetch_own_profile(): void
    {
        $response = $this->getJson('/api/auth/me', $this->asOfficer());

        $response->assertOk()
            ->assertJsonPath('data.id', TestingSeeder::OFFICER_ID)
            ->assertJsonPath('data.role', 'officer')
            ->assertJsonPath('data.email', 'officer@test.com');
    }

    /** @test */
    public function profile_response_omits_password(): void
    {
        $response = $this->getJson('/api/auth/me', $this->asOfficer());

        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    /** @test */
    public function user_can_logout_and_token_is_revoked(): void
    {
        $headers = $this->asOfficer();

        $this->postJson('/api/auth/logout', [], $headers)
            ->assertOk()
            ->assertJsonFragment(['message' => 'Logged out successfully.']);

        // Token should now be invalid
        $this->getJson('/api/loans', $headers)
            ->assertUnauthorized();
    }

    // ── Role labels in response ───────────────────────────────────────────────

    /** @test */
    public function login_response_includes_correct_role_for_each_staff_type(): void
    {
        $cases = [
            ['officer@test.com',    'officer'],
            ['manager@test.com',    'manager'],
            ['ceo@test.com',        'ceo'],
            ['superadmin@test.com', 'superadmin'],
            ['accountant@test.com', 'accountant'],
        ];

        foreach ($cases as [$email, $expectedRole]) {
            $response = $this->postJson('/api/auth/login', [
                'email'    => $email,
                'password' => 'Password1',
            ]);
            $response->assertOk();
            $this->assertEquals($expectedRole, $response->json('user.role'), "Role mismatch for {$email}");
        }
    }
}
<?php

// ═══════════════════════════════════════════════════════════════════════════════
// BorrowerTest
// File: tests/Feature/Borrowers/BorrowerTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Borrowers;
