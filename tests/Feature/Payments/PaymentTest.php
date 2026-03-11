<?php

namespace Tests\Feature\Payments;

use App\Models\Penalty;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

class PaymentTest extends GracimorTestCase
{
    // ── Record payment ────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_record_a_payment(): void
    {
        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(),
            $this->asOfficer()
        );

        $response->assertCreated()
            ->assertJsonPath('data.amount', 4907.13)
            ->assertJsonPath('data.payment_method', 'cash')
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.recorded_by.id', TestingSeeder::OFFICER_ID);

        $this->assertDatabaseHas('payments', [
            'loan_id'        => TestingSeeder::LOAN_ACTIVE_ID,
            'amount'         => 4907.13,
            'payment_method' => 'cash',
            'status'         => 'paid',
        ]);
    }

    /** @test */
    public function accountant_cannot_record_a_payment(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(),
            $this->asAccountant()
        )->assertForbidden();
    }

    /** @test */
    public function payment_requires_mandatory_fields(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            [],
            $this->asOfficer()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['amount', 'payment_method', 'payment_date']);
    }

    /** @test */
    public function payment_amount_must_be_positive(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(['amount' => -100]),
            $this->asOfficer()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function payment_date_cannot_be_in_the_future(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(['payment_date' => now()->addDays(5)->format('Y-m-d')]),
            $this->asOfficer()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['payment_date']);
    }

    /** @test */
    public function bank_transfer_payment_requires_reference(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(['payment_method' => 'bank_transfer', 'reference' => null]),
            $this->asOfficer()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['reference']);
    }

    /** @test */
    public function payment_cannot_be_recorded_on_closed_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_CLOSED_ID . '/payments',
            $this->validPaymentPayload(),
            $this->asOfficer()
        )->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Cannot record payment on a closed loan.']);
    }

    /** @test */
    public function payment_cannot_be_recorded_on_pending_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_PENDING_ID . '/payments',
            $this->validPaymentPayload(),
            $this->asOfficer()
        )->assertUnprocessable();
    }

    // ── Allocation breakdown ──────────────────────────────────────────────────

    /** @test */
    public function payment_response_includes_allocation_breakdown(): void
    {
        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/payments',
            $this->validPaymentPayload(['amount' => 5000]),
            $this->asOfficer()
        );

        $response->assertCreated();

        // Overdue loan has penalties — allocation should cover penalty first
        $allocations = $response->json('data.allocations');
        $this->assertNotNull($allocations);
        $this->assertArrayHasKey('penalty', $allocations);
        $this->assertArrayHasKey('interest', $allocations);
        $this->assertArrayHasKey('principal', $allocations);

        // All allocations should sum to the payment amount
        $total = $allocations['penalty'] + $allocations['interest'] + $allocations['principal'];
        $this->assertEqualsWithDelta(5000, $total, 0.01);
    }

    /** @test */
    public function payment_updates_loan_balance(): void
    {
        $balanceBefore = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->value('total_outstanding');

        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(['amount' => 4907.13]),
            $this->asOfficer()
        )->assertCreated();

        $balanceAfter = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->value('total_outstanding');

        $this->assertLessThan($balanceBefore, $balanceAfter);
    }

    /** @test */
    public function receipt_number_is_unique_per_payment(): void
    {
        $first = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(),
            $this->asOfficer()
        )->json('data.receipt_number');

        $second = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->validPaymentPayload(),
            $this->asOfficer()
        )->json('data.receipt_number');

        $this->assertNotEquals($first, $second);
    }

    // ── Reversal ──────────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_reverse_a_payment(): void
    {
        // Get an existing payment on the active loan
        $payment = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->first();

        $response = $this->postJson(
            "/api/payments/{$payment->id}/reverse",
            ['reversal_reason' => 'Duplicate entry.'],
            $this->asManager()
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'reversed');

        $this->assertDatabaseHas('payments', [
            'id'              => $payment->id,
            'status'          => 'reversed',
            'reversal_reason' => 'Duplicate entry.',
        ]);
    }

    /** @test */
    public function reversal_requires_a_reason(): void
    {
        $payment = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->first();

        $this->postJson(
            "/api/payments/{$payment->id}/reverse",
            [],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['reversal_reason']);
    }

    /** @test */
    public function officer_cannot_reverse_a_payment(): void
    {
        $payment = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->first();

        $this->postJson(
            "/api/payments/{$payment->id}/reverse",
            ['reversal_reason' => 'Test.'],
            $this->asOfficer()
        )->assertForbidden();
    }

    /** @test */
    public function already_reversed_payment_cannot_be_reversed_again(): void
    {
        $payment = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->first();

        // First reversal
        $this->postJson(
            "/api/payments/{$payment->id}/reverse",
            ['reversal_reason' => 'First reversal.'],
            $this->asManager()
        )->assertOk();

        // Second reversal attempt
        $this->postJson(
            "/api/payments/{$payment->id}/reverse",
            ['reversal_reason' => 'Second reversal.'],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Payment is already reversed.']);
    }

    /** @test */
    public function reversal_restores_loan_balance(): void
    {
        $payment = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->first();

        $balanceBefore = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->value('total_outstanding');

        $this->postJson(
            "/api/payments/{$payment->id}/reverse",
            ['reversal_reason' => 'Error.'],
            $this->asManager()
        )->assertOk();

        $balanceAfter = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->value('total_outstanding');

        $this->assertGreaterThan($balanceBefore, $balanceAfter);
    }

    // ── Receipt endpoint ──────────────────────────────────────────────────────

    /** @test */
    public function officer_can_fetch_payment_receipt(): void
    {
        $payment = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->first();

        $response = $this->getJson(
            "/api/payments/{$payment->id}/receipt",
            $this->asOfficer()
        );

        $response->assertOk()
            ->assertJsonStructure([
                'receipt_number', 'amount', 'payment_date',
                'payment_method', 'loan_number', 'borrower_name',
            ]);
    }

    // ── List payments on loan ─────────────────────────────────────────────────

    /** @test */
    public function officer_can_list_payments_on_a_loan(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/payments',
            $this->asOfficer()
        );

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'amount', 'payment_date', 'status']]]);

        // Loan 1 has 5 seeded payments
        $this->assertCount(5, $response->json('data'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validPaymentPayload(array $overrides = []): array
    {
        return array_merge([
            'amount'         => 4907.13,
            'payment_method' => 'cash',
            'payment_date'   => now()->format('Y-m-d'),
        ], $overrides);
    }
}
<?php

// ═══════════════════════════════════════════════════════════════════════════════
// PenaltyTest
// File: tests/Feature/Penalties/PenaltyTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Penalties;
