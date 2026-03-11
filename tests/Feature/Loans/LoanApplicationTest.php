<?php

namespace Tests\Feature\Loans;

use App\Models\CollateralAsset;
use App\Models\Loan;
use App\Models\LoanStatusHistory;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

class LoanApplicationTest extends GracimorTestCase
{
    // ── List ──────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_list_loans(): void
    {
        $this->getJson('/api/loans', $this->asOfficer())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'loan_number', 'status', 'principal_amount']],
            ]);
    }

    /** @test */
    public function loan_list_can_be_filtered_by_status(): void
    {
        $response = $this->getJson('/api/loans?status=active', $this->asOfficer());
        $response->assertOk();
        foreach ($response->json('data') as $loan) {
            $this->assertEquals('active', $loan['status']);
        }
    }

    /** @test */
    public function loan_list_contains_all_seeded_statuses(): void
    {
        $response  = $this->getJson('/api/loans', $this->asOfficer());
        $statuses  = collect($response->json('data'))->pluck('status')->unique()->values()->all();
        sort($statuses);
        $this->assertContains('active',           $statuses);
        $this->assertContains('overdue',          $statuses);
        $this->assertContains('pending_approval', $statuses);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_view_a_loan(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asOfficer()
        );

        $response->assertOk()
            ->assertJsonPath('data.id', TestingSeeder::LOAN_ACTIVE_ID)
            ->assertJsonPath('data.loan_number', 'GRS-TEST-00001')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.principal_amount', 50000);
    }

    /** @test */
    public function loan_response_includes_borrower_and_product(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID,
            $this->asOfficer()
        );

        $response->assertJsonPath('data.borrower.id', TestingSeeder::BORROWER_VERIFIED_ID)
                 ->assertJsonPath('data.loan_product.code', 'TVL01');
    }

    // ── Apply ─────────────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_apply_for_a_loan(): void
    {
        $payload = $this->validApplicationPayload();

        $response = $this->postJson('/api/loans', $payload, $this->asOfficer());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending_approval')
            ->assertJsonPath('data.principal_amount', 20000)
            ->assertJsonPath('data.applied_by.id', TestingSeeder::OFFICER_ID);

        $this->assertDatabaseHas('loans', [
            'borrower_id'     => TestingSeeder::BORROWER_VERIFIED_ID,
            'status'          => 'pending_approval',
            'principal_amount'=> 20000,
        ]);
    }

    /** @test */
    public function accountant_cannot_apply_for_a_loan(): void
    {
        $this->postJson('/api/loans', $this->validApplicationPayload(), $this->asAccountant())
            ->assertForbidden();
    }

    /** @test */
    public function loan_application_requires_mandatory_fields(): void
    {
        $this->postJson('/api/loans', [], $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'borrower_id', 'loan_product_id', 'collateral_asset_id',
                'principal_amount', 'term_months',
            ]);
    }

    /** @test */
    public function loan_application_fails_if_borrower_kyc_not_verified(): void
    {
        // Borrower 2 has kyc_status = pending
        $payload = $this->validApplicationPayload([
            'borrower_id' => TestingSeeder::BORROWER_PENDING_ID,
        ]);

        $this->postJson('/api/loans', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Borrower KYC is not verified.']);
    }

    /** @test */
    public function loan_application_fails_if_borrower_already_has_active_loan(): void
    {
        // Borrower 1 already has an active loan (loan 1)
        $payload = $this->validApplicationPayload([
            'borrower_id'       => TestingSeeder::BORROWER_VERIFIED_ID,
            'collateral_asset_id' => TestingSeeder::ASSET_VEHICLE_AVAILABLE_ID,
        ]);

        $this->postJson('/api/loans', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Borrower already has an active loan.']);
    }

    /** @test */
    public function loan_application_fails_if_principal_exceeds_ltv(): void
    {
        // Asset 1 is worth K280,000, TVL01 LTV = 70% → max K196,000
        // Requesting K250,000 should fail
        $payload = $this->validApplicationPayload([
            'borrower_id'         => TestingSeeder::BORROWER_VERIFIED_ID,
            'collateral_asset_id' => TestingSeeder::ASSET_VEHICLE_PLEDGED_ID,
            'principal_amount'    => 250000,
        ]);

        $this->postJson('/api/loans', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Principal exceeds maximum LTV for this collateral.']);
    }

    /** @test */
    public function loan_application_fails_if_principal_below_product_minimum(): void
    {
        $payload = $this->validApplicationPayload(['principal_amount' => 500]);

        $this->postJson('/api/loans', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['principal_amount']);
    }

    /** @test */
    public function loan_application_fails_if_collateral_already_pledged(): void
    {
        // Asset 1 is already pledged to loan 1
        $payload = $this->validApplicationPayload([
            'borrower_id'         => TestingSeeder::BORROWER_VERIFIED_ID,
            'collateral_asset_id' => TestingSeeder::ASSET_VEHICLE_PLEDGED_ID,
        ]);

        $this->postJson('/api/loans', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Collateral asset is already pledged to another loan.']);
    }

    /** @test */
    public function loan_application_fails_if_term_exceeds_product_maximum(): void
    {
        // TVL01 max_term_months = 24
        $payload = $this->validApplicationPayload(['term_months' => 36]);

        $this->postJson('/api/loans', $payload, $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['term_months']);
    }

    /** @test */
    public function loan_application_response_includes_calculated_schedule(): void
    {
        $payload = $this->validApplicationPayload([
            'borrower_id'         => TestingSeeder::BORROWER_VERIFIED_ID,
            'collateral_asset_id' => TestingSeeder::ASSET_VEHICLE_AVAILABLE_ID,
        ]);

        // Use borrower 3 who has no active loan
        $payload['borrower_id'] = TestingSeeder::BORROWER_NOEMAIL_ID;

        $response = $this->postJson('/api/loans', $payload, $this->asOfficer());
        $response->assertCreated();

        // Response should contain the computed instalment
        $this->assertGreaterThan(0, $response->json('data.monthly_instalment'));
        $this->assertGreaterThan(0, $response->json('data.total_repayable'));
    }

    // ── Calculator endpoint ───────────────────────────────────────────────────

    /** @test */
    public function loan_calculator_returns_correct_instalment(): void
    {
        // K50,000 at 28% reducing balance over 12 months → K4,907.13
        $response = $this->postJson('/api/loans/calculate', [
            'principal_amount' => 50000,
            'interest_rate'    => 28,
            'interest_method'  => 'reducing_balance',
            'term_months'      => 12,
        ], $this->asOfficer());

        $response->assertOk();
        $this->assertEqualsWithDelta(4907.13, $response->json('monthly_instalment'), 1.00);
        $this->assertEqualsWithDelta(58885.56, $response->json('total_repayable'), 5.00);
    }

    /** @test */
    public function loan_calculator_validates_required_fields(): void
    {
        $this->postJson('/api/loans/calculate', [], $this->asOfficer())
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'principal_amount', 'interest_rate', 'interest_method', 'term_months',
            ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validApplicationPayload(array $overrides = []): array
    {
        return array_merge([
            'borrower_id'         => TestingSeeder::BORROWER_NOEMAIL_ID,
            'loan_product_id'     => TestingSeeder::PRODUCT_VEHICLE_ID,
            'collateral_asset_id' => TestingSeeder::ASSET_VEHICLE_AVAILABLE_ID,
            'principal_amount'    => 20000,
            'term_months'         => 6,
            'loan_purpose'        => 'Business working capital',
        ], $overrides);
    }
}
<?php

// ═══════════════════════════════════════════════════════════════════════════════
// LoanApprovalTest
// File: tests/Feature/Loans/LoanApprovalTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Loans;
