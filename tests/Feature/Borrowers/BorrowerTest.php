<?php

namespace Tests\Feature\Borrowers;

use App\Models\Loan;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

class BorrowerTest extends GracimorTestCase
{
    // ── Index ─────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_list_borrowers(): void
    {
        $this->getJson('/api/borrowers', $this->asOfficer())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'borrower_number', 'first_name', 'last_name', 'kyc_status']],
                'meta' => ['total', 'per_page'],
            ]);
    }

    /** @test */
    public function accountant_can_list_borrowers(): void
    {
        $this->getJson('/api/borrowers', $this->asAccountant())
            ->assertOk();
    }

    /** @test */
    public function borrower_list_contains_seeded_records(): void
    {
        $response = $this->getJson('/api/borrowers', $this->asOfficer());
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains(TestingSeeder::BORROWER_VERIFIED_ID, $ids);
    }

    /** @test */
    public function borrower_list_can_be_filtered_by_kyc_status(): void
    {
        $response = $this->getJson('/api/borrowers?kyc_status=pending', $this->asOfficer());
        $response->assertOk();
        foreach ($response->json('data') as $item) {
            $this->assertEquals('pending', $item['kyc_status']);
        }
    }

    /** @test */
    public function borrower_list_can_be_searched_by_name(): void
    {
        $response = $this->getJson('/api/borrowers?search=Mwansa', $this->asOfficer());
        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_view_a_borrower(): void
    {
        $response = $this->getJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            $this->asOfficer()
        );

        $response->assertOk()
            ->assertJsonPath('data.id', TestingSeeder::BORROWER_VERIFIED_ID)
            ->assertJsonPath('data.first_name', 'Mwansa')
            ->assertJsonPath('data.last_name', 'Chanda')
            ->assertJsonPath('data.kyc_status', 'verified');
    }

    /** @test */
    public function viewing_nonexistent_borrower_returns_404(): void
    {
        $this->getJson('/api/borrowers/99999', $this->asOfficer())
            ->assertNotFound();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_create_a_borrower(): void
    {
        $payload = $this->validBorrowerPayload();

        $response = $this->postJson('/api/borrowers', $payload, $this->asOfficer());

        $response->assertCreated()
            ->assertJsonPath('data.first_name', 'Kaputo')
            ->assertJsonPath('data.last_name', 'Sichone')
            ->assertJsonPath('data.kyc_status', 'pending');

        $this->assertDatabaseHas('borrowers', [
            'first_name' => 'Kaputo',
            'last_name'  => 'Sichone',
            'nrc_number' => '987654/32/1',
        ]);
    }

    /** @test */
    public function accountant_cannot_create_a_borrower(): void
    {
        $this->postJson('/api/borrowers', $this->validBorrowerPayload(), $this->asAccountant())
            ->assertForbidden();
    }

    /** @test */
    public function borrower_creation_requires_mandatory_fields(): void
    {
        $this->postJson('/api/borrowers', [], $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name', 'last_name', 'nrc_number',
                'date_of_birth', 'gender', 'phone_primary',
                'residential_address', 'city_town',
            ]);
    }

    /** @test */
    public function duplicate_nrc_number_is_rejected(): void
    {
        $payload = $this->validBorrowerPayload([
            'nrc_number' => '123456/78/1', // already seeded for borrower 1
        ]);

        $this->postJson('/api/borrowers', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nrc_number']);
    }

    /** @test */
    public function duplicate_primary_phone_is_rejected(): void
    {
        $payload = $this->validBorrowerPayload([
            'phone_primary' => '+260977111001', // already seeded
        ]);

        $this->postJson('/api/borrowers', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone_primary']);
    }

    /** @test */
    public function invalid_nrc_format_is_rejected(): void
    {
        $payload = $this->validBorrowerPayload(['nrc_number' => 'BADFORMAT']);

        $this->postJson('/api/borrowers', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nrc_number']);
    }

    /** @test */
    public function invalid_zambian_phone_is_rejected(): void
    {
        $payload = $this->validBorrowerPayload(['phone_primary' => '+44207000000']);

        $this->postJson('/api/borrowers', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone_primary']);
    }

    /** @test */
    public function underage_borrower_is_rejected(): void
    {
        $payload = $this->validBorrowerPayload([
            'date_of_birth' => now()->subYears(17)->format('Y-m-d'),
        ]);

        $this->postJson('/api/borrowers', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_of_birth']);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_update_contact_fields(): void
    {
        $this->putJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            ['phone_primary' => '+260977999001'],
            $this->asOfficer()
        )->assertOk()
         ->assertJsonPath('data.phone_primary', '+260977999001');

        $this->assertDatabaseHas('borrowers', [
            'id'            => TestingSeeder::BORROWER_VERIFIED_ID,
            'phone_primary' => '+260977999001',
        ]);
    }

    /** @test */
    public function officer_cannot_set_kyc_status_directly(): void
    {
        // Updating kyc_status must go through the dedicated KYC verification endpoint
        $this->putJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_PENDING_ID,
            ['kyc_status' => 'verified'],
            $this->asOfficer()
        )->assertForbidden();
    }

    /** @test */
    public function manager_can_verify_kyc(): void
    {
        $this->postJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_PENDING_ID . '/verify-kyc',
            ['notes' => 'Documents verified in branch.'],
            $this->asManager()
        )->assertOk()
         ->assertJsonPath('data.kyc_status', 'verified');

        $this->assertDatabaseHas('borrowers', [
            'id'         => TestingSeeder::BORROWER_PENDING_ID,
            'kyc_status' => 'verified',
        ]);
    }

    /** @test */
    public function officer_cannot_verify_kyc(): void
    {
        $this->postJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_PENDING_ID . '/verify-kyc',
            [],
            $this->asOfficer()
        )->assertForbidden();
    }

    // ── Role-gated fields ─────────────────────────────────────────────────────

    /** @test */
    public function officer_response_omits_internal_notes(): void
    {
        // internal_notes is a manager+ field
        $response = $this->getJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            $this->asOfficer()
        );
        $this->assertArrayNotHasKey('internal_notes', $response->json('data'));
    }

    /** @test */
    public function manager_response_includes_internal_notes(): void
    {
        $response = $this->getJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            $this->asManager()
        );
        $this->assertArrayHasKey('internal_notes', $response->json('data'));
    }

    // ── Soft delete / deactivate ──────────────────────────────────────────────

    /** @test */
    public function borrower_with_active_loan_cannot_be_deleted(): void
    {
        // Borrower 1 has an active loan (loan ID 1)
        $this->deleteJson(
            '/api/borrowers/' . TestingSeeder::BORROWER_VERIFIED_ID,
            [],
            $this->asManager()
        )->assertConflict(); // 409
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validBorrowerPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name'          => 'Kaputo',
            'last_name'           => 'Sichone',
            'nrc_number'          => '987654/32/1',
            'date_of_birth'       => '1988-04-10',
            'gender'              => 'male',
            'phone_primary'       => '+260977888001',
            'residential_address' => 'Plot 9999, Great East Road, Lusaka',
            'city_town'           => 'Lusaka',
            'employment_status'   => 'employed',
            'employer_name'       => 'Test Employer Ltd',
            'monthly_income'      => 8000,
        ], $overrides);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanApplicationTest
// File: tests/Feature/Loans/LoanApplicationTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Loans;
