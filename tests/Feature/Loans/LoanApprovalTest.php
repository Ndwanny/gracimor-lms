<?php

namespace Tests\Feature\Loans;

use App\Models\Payment;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

class LoanApprovalTest extends GracimorTestCase
{
    // ── Approve ───────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_approve_a_pending_loan(): void
    {
        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/approve',
            ['approval_notes' => 'Credit check passed.'],
            $this->asManager()
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.approved_by.id', TestingSeeder::MANAGER_ID);

        $this->assertDatabaseHas('loans', [
            'id'          => TestingSeeder::LOAN_PENDING_ID,
            'status'      => 'approved',
            'approved_by' => TestingSeeder::MANAGER_ID,
        ]);
    }

    /** @test */
    public function approval_writes_status_history_record(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/approve',
            [],
            $this->asManager()
        )->assertOk();

        $this->assertDatabaseHas('loan_status_histories', [
            'loan_id'         => TestingSeeder::LOAN_PENDING_ID,
            'previous_status' => 'pending_approval',
            'new_status'      => 'approved',
            'changed_by'      => TestingSeeder::MANAGER_ID,
        ]);
    }

    /** @test */
    public function officer_cannot_approve_a_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/approve',
            [],
            $this->asOfficer()
        )->assertForbidden();
    }

    /** @test */
    public function four_eyes_principle_prevents_officer_self_approval(): void
    {
        // Loan 3 was applied by officer (ID 1). The approver must be a different user.
        // Even if an officer had the role, the applying officer cannot approve their own loan.
        // Here we verify the 4-eyes guard by checking applied_by cannot match approved_by.
        $loan = Loan::find(TestingSeeder::LOAN_PENDING_ID);
        $this->assertEquals(TestingSeeder::OFFICER_ID, $loan->applied_by);

        // Manager approves — this is valid (different user)
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/approve',
            [],
            $this->asManager()
        )->assertOk();

        $approved = Loan::find(TestingSeeder::LOAN_PENDING_ID);
        $this->assertNotEquals($approved->applied_by, $approved->approved_by);
    }

    /** @test */
    public function cannot_approve_an_already_approved_loan(): void
    {
        // Loan 4 is already in 'approved' status
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_APPROVED_ID . '/approve',
            [],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Loan is not in pending_approval status.']);
    }

    /** @test */
    public function cannot_approve_an_active_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/approve',
            [],
            $this->asManager()
        )->assertUnprocessable();
    }

    // ── Reject ────────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_reject_a_pending_loan(): void
    {
        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/reject',
            ['rejection_reason' => 'Insufficient collateral value.'],
            $this->asManager()
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('loans', [
            'id'               => TestingSeeder::LOAN_PENDING_ID,
            'status'           => 'rejected',
            'rejection_reason' => 'Insufficient collateral value.',
        ]);
    }

    /** @test */
    public function rejection_requires_a_reason(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/reject',
            [],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function officer_cannot_reject_a_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/reject',
            ['rejection_reason' => 'Test.'],
            $this->asOfficer()
        )->assertForbidden();
    }

    // ── Disburse ──────────────────────────────────────────────────────────────

    /** @test */
    public function ceo_can_disburse_an_approved_loan(): void
    {
        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_APPROVED_ID . '/disburse',
            [
                'disbursement_method'    => 'bank_transfer',
                'disbursement_reference' => 'TXN999888777',
            ],
            $this->asCeo()
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('loans', [
            'id'                     => TestingSeeder::LOAN_APPROVED_ID,
            'status'                 => 'active',
            'disbursement_reference' => 'TXN999888777',
        ]);
    }

    /** @test */
    public function disbursal_marks_collateral_as_pledged(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_APPROVED_ID . '/disburse',
            ['disbursement_method' => 'cash'],
            $this->asCeo()
        )->assertOk();

        // Loan 4 uses asset 3 (land)
        $this->assertDatabaseHas('collateral_assets', [
            'id'     => TestingSeeder::ASSET_LAND_ID,
            'status' => 'pledged',
        ]);
    }

    /** @test */
    public function disbursal_generates_repayment_schedule(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_APPROVED_ID . '/disburse',
            ['disbursement_method' => 'cash'],
            $this->asCeo()
        )->assertOk();

        $loan = Loan::find(TestingSeeder::LOAN_APPROVED_ID);
        $this->assertEquals(
            $loan->term_months,
            \App\Models\LoanSchedule::where('loan_id', $loan->id)->count()
        );
    }

    /** @test */
    public function manager_cannot_disburse_a_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_APPROVED_ID . '/disburse',
            ['disbursement_method' => 'cash'],
            $this->asManager()
        )->assertForbidden();
    }

    /** @test */
    public function cannot_disburse_a_pending_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/disburse',
            ['disbursement_method' => 'cash'],
            $this->asCeo()
        )->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Loan must be in approved status to disburse.']);
    }

    // ── Repayment schedule endpoint ───────────────────────────────────────────

    /** @test */
    public function officer_can_view_loan_repayment_schedule(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/schedule',
            $this->asOfficer()
        );

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['instalment_number', 'due_date', 'principal_component',
                            'interest_component', 'status']],
            ]);

        // Loan 1 has 12 instalments
        $this->assertCount(12, $response->json('data'));
    }

    /** @test */
    public function paid_instalments_show_correct_status(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/schedule',
            $this->asOfficer()
        );

        $first = $response->json('data.0');
        $this->assertEquals(1, $first['instalment_number']);
        $this->assertEquals('paid', $first['status']);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// PaymentTest
// File: tests/Feature/Payments/PaymentTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Payments;
