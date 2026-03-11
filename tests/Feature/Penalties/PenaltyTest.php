<?php

namespace Tests\Feature\Penalties;

use App\Console\Commands\UpdateOverdueStatusesCommand;
use App\Models\CollateralAsset;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\LoanStatusHistory;
use Carbon\Carbon;
use Database\Seeders\TestingSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\GracimorTestCase;

class PenaltyTest extends GracimorTestCase
{
    // ── List penalties ────────────────────────────────────────────────────────

    /** @test */
    public function officer_can_list_penalties_on_an_overdue_loan(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties',
            $this->asOfficer()
        );

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'amount', 'status', 'days_overdue', 'applied_at']],
            ]);

        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /** @test */
    public function active_loan_with_no_penalties_returns_empty_list(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/penalties',
            $this->asOfficer()
        );

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }

    /** @test */
    public function penalty_list_shows_correct_total_on_overdue_loan(): void
    {
        $response = $this->getJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties',
            $this->asOfficer()
        );

        $listTotal = collect($response->json('data'))
            ->where('status', 'outstanding')
            ->sum('amount');

        $balancePenalty = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->value('penalty_balance');

        $this->assertEqualsWithDelta($balancePenalty, $listTotal, 0.01);
    }

    // ── Single waiver ─────────────────────────────────────────────────────────

    /** @test */
    public function manager_can_waive_a_single_penalty(): void
    {
        $penalty = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->first();

        $response = $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            [
                'waiver_reason' => 'hardship',
                'waiver_notes'  => 'Borrower demonstrated financial hardship.',
            ],
            $this->asManager()
        );

        $response->assertOk()
            ->assertJsonPath('data.status', 'waived')
            ->assertJsonPath('data.waiver_reason', 'hardship');

        $this->assertDatabaseHas('penalties', [
            'id'            => $penalty->id,
            'status'        => 'waived',
            'waiver_reason' => 'hardship',
            'waived_by'     => TestingSeeder::MANAGER_ID,
        ]);
    }

    /** @test */
    public function waiving_a_penalty_reduces_loan_balance(): void
    {
        $penalty = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->first();

        $penaltyAmount = $penalty->amount;

        $balanceBefore = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->value('penalty_balance');

        $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            ['waiver_reason' => 'error'],
            $this->asManager()
        )->assertOk();

        $balanceAfter = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->value('penalty_balance');

        $this->assertEqualsWithDelta(
            $balanceBefore - $penaltyAmount,
            $balanceAfter,
            0.01
        );
    }

    /** @test */
    public function officer_cannot_waive_a_penalty(): void
    {
        $penalty = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->first();

        $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            ['waiver_reason' => 'hardship'],
            $this->asOfficer()
        )->assertForbidden();
    }

    /** @test */
    public function waiver_requires_a_valid_reason(): void
    {
        $penalty = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->first();

        $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            ['waiver_reason' => 'made_up_reason'],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['waiver_reason']);
    }

    /** @test */
    public function waiver_requires_reason_field(): void
    {
        $penalty = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->first();

        $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            [],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonValidationErrors(['waiver_reason']);
    }

    /** @test */
    public function already_waived_penalty_cannot_be_waived_again(): void
    {
        $penalty = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->first();

        // First waiver
        $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            ['waiver_reason' => 'hardship'],
            $this->asManager()
        )->assertOk();

        // Second waiver
        $this->postJson(
            "/api/penalties/{$penalty->id}/waive",
            ['waiver_reason' => 'error'],
            $this->asManager()
        )->assertUnprocessable()
         ->assertJsonFragment(['message' => 'Penalty is already waived or paid.']);
    }

    // ── Bulk waiver ───────────────────────────────────────────────────────────

    /** @test */
    public function ceo_can_bulk_waive_all_penalties_on_a_loan(): void
    {
        $outstandingCount = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->count();

        $this->assertGreaterThan(0, $outstandingCount);

        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties/bulk-waive',
            [
                'waiver_reason' => 'management_decision',
                'waiver_notes'  => 'Board approved full penalty waiver.',
            ],
            $this->asCeo()
        );

        $response->assertOk()
            ->assertJsonPath('waived_count', $outstandingCount);

        // All outstanding penalties should now be waived
        $remaining = Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->count();

        $this->assertEquals(0, $remaining);
    }

    /** @test */
    public function bulk_waiver_zeros_penalty_balance_on_loan(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties/bulk-waive',
            ['waiver_reason' => 'management_decision'],
            $this->asCeo()
        )->assertOk();

        $penaltyBalance = \App\Models\LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->value('penalty_balance');

        $this->assertEqualsWithDelta(0.0, $penaltyBalance, 0.01);
    }

    /** @test */
    public function manager_cannot_bulk_waive_penalties(): void
    {
        $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_OVERDUE_ID . '/penalties/bulk-waive',
            ['waiver_reason' => 'management_decision'],
            $this->asManager()
        )->assertForbidden();
    }

    /** @test */
    public function bulk_waive_on_loan_with_no_outstanding_penalties_returns_zero(): void
    {
        $response = $this->postJson(
            '/api/loans/' . TestingSeeder::LOAN_ACTIVE_ID . '/penalties/bulk-waive',
            ['waiver_reason' => 'management_decision'],
            $this->asCeo()
        );

        $response->assertOk()
            ->assertJsonPath('waived_count', 0);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// OverdueCommandTest
// File: tests/Feature/Commands/OverdueCommandTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Commands;
