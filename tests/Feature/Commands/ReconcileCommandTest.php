<?php

namespace Tests\Feature\Commands;

use App\Models\SmsTemplate;
use Database\Seeders\SmsTemplateSeeder;
use Database\Seeders\TestingSeeder;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\GracimorTestCase;

class ReconcileCommandTest extends GracimorTestCase
{
    // ── Correct balance ───────────────────────────────────────────────────────

    /** @test */
    public function command_runs_successfully_on_clean_data(): void
    {
        $this->artisan('app:reconcile-loan-balances')
            ->assertExitCode(0);
    }

    /** @test */
    public function command_processes_all_disbursed_loans(): void
    {
        $this->artisan('app:reconcile-loan-balances')
            ->expectsOutputToContain('processed')
            ->assertExitCode(0);
    }

    // ── Mismatch detection and correction ─────────────────────────────────────

    /** @test */
    public function command_corrects_a_stale_total_paid_balance(): void
    {
        // Corrupt the stored total_paid on loan 1
        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_paid' => 0.00]); // wrong — should be ~24,535

        $this->artisan('app:reconcile-loan-balances')->assertExitCode(0);

        $balance = LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)->first();
        $actualPaid = Payment::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->where('status', 'paid')
            ->sum('amount');

        $this->assertEqualsWithDelta($actualPaid, $balance->total_paid, 0.01);
    }

    /** @test */
    public function command_corrects_inflated_penalty_balance(): void
    {
        // Inflate penalty_balance beyond what Penalty records show
        LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->update(['penalty_balance' => 999999.00]);

        $this->artisan('app:reconcile-loan-balances')->assertExitCode(0);

        $balance = LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)->first();

        // After reconcile, penalty_balance should match sum of outstanding penalties
        $expectedPenalties = \App\Models\Penalty::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('status', 'outstanding')
            ->sum('amount');

        $this->assertEqualsWithDelta($expectedPenalties, $balance->penalty_balance, 0.01);
    }

    /** @test */
    public function command_zeros_balance_for_closed_loan(): void
    {
        // Corrupt a closed loan's balance
        LoanBalance::where('loan_id', TestingSeeder::LOAN_CLOSED_ID)
            ->update([
                'principal_balance' => 5000.00,
                'interest_balance'  => 1000.00,
                'total_outstanding' => 6000.00,
            ]);

        $this->artisan('app:reconcile-loan-balances')->assertExitCode(0);

        $balance = LoanBalance::where('loan_id', TestingSeeder::LOAN_CLOSED_ID)->first();
        $this->assertEquals(0.00, $balance->principal_balance);
        $this->assertEquals(0.00, $balance->interest_balance);
        $this->assertEquals(0.00, $balance->total_outstanding);
    }

    /** @test */
    public function reconcile_creates_balance_record_if_missing(): void
    {
        // Delete balance for active loan to simulate missing record
        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)->delete();

        $this->artisan('app:reconcile-loan-balances')->assertExitCode(0);

        $balance = LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)->first();
        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_paid);
    }

    // ── Single-loan mode ──────────────────────────────────────────────────────

    /** @test */
    public function loan_option_restricts_reconcile_to_single_loan(): void
    {
        // Corrupt both loans; run with --loan= targeting only one
        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_paid' => 0.00]);
        LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->update(['total_paid' => 0.00]);

        $this->artisan('app:reconcile-loan-balances --loan=' . TestingSeeder::LOAN_ACTIVE_ID)
            ->assertExitCode(0);

        // Active loan should be corrected
        $activePaid = LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)->value('total_paid');
        $this->assertGreaterThan(0, $activePaid);

        // Overdue loan should still be corrupt (not processed)
        $overduePaid = LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)->value('total_paid');
        $this->assertEquals(0.00, $overduePaid);
    }

    // ── Dry-run ───────────────────────────────────────────────────────────────

    /** @test */
    public function dry_run_reports_mismatch_without_writing(): void
    {
        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_paid' => 0.00]);

        $this->artisan('app:reconcile-loan-balances --dry-run')
            ->expectsOutputToContain('DRY RUN')
            ->assertExitCode(0);

        // Balance should remain corrupt — dry run wrote nothing
        $storedPaid = LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)->value('total_paid');
        $this->assertEquals(0.00, $storedPaid);
    }

    // ── Mismatch-only mode ────────────────────────────────────────────────────

    /** @test */
    public function mismatch_option_only_updates_divergent_balances(): void
    {
        // Corrupt only the active loan
        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_paid' => 1.00]);

        $closedBefore = LoanBalance::where('loan_id', TestingSeeder::LOAN_CLOSED_ID)
            ->value('recalculated_at');

        $this->artisan('app:reconcile-loan-balances --mismatch')->assertExitCode(0);

        // Active loan's total_paid was mismatched — should be corrected
        $activePaid = LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)->value('total_paid');
        $this->assertGreaterThan(1.00, $activePaid);

        // Closed loan was not mismatched — recalculated_at should be unchanged
        $closedAfter = LoanBalance::where('loan_id', TestingSeeder::LOAN_CLOSED_ID)
            ->value('recalculated_at');

        $this->assertEquals($closedBefore, $closedAfter);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// SmsTemplateTest
// File: tests/Feature/Sms/SmsTemplateTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Sms;
