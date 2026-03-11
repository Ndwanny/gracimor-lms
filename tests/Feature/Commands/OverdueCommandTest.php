<?php

namespace Tests\Feature\Commands;

use App\Models\LoanBalance;
use App\Models\Payment;
use Database\Seeders\TestingSeeder;
use Tests\Feature\GracimorTestCase;

class OverdueCommandTest extends GracimorTestCase
{
    // ── Mark overdue schedules ────────────────────────────────────────────────

    /** @test */
    public function command_marks_past_due_schedule_rows_as_overdue(): void
    {
        // Create a pending schedule row with a past due date
        $loan = Loan::find(TestingSeeder::LOAN_ACTIVE_ID);
        $schedule = LoanSchedule::create([
            'loan_id'             => $loan->id,
            'instalment_number'   => 99,
            'due_date'            => Carbon::now()->subDays(10)->format('Y-m-d'),
            'principal_component' => 3000,
            'interest_component'  => 1000,
            'principal_paid'      => 0,
            'interest_paid'       => 0,
            'opening_balance'     => 20000,
            'closing_balance'     => 17000,
            'status'              => 'pending',
        ]);

        $this->artisan('app:update-overdue-statuses')
            ->assertExitCode(0);

        $schedule->refresh();
        $this->assertEquals('overdue', $schedule->status);
    }

    /** @test */
    public function command_marks_active_loan_overdue_when_schedule_is_overdue(): void
    {
        // Inject a past-due schedule into the active loan
        LoanSchedule::create([
            'loan_id'             => TestingSeeder::LOAN_ACTIVE_ID,
            'instalment_number'   => 99,
            'due_date'            => Carbon::now()->subDays(5)->format('Y-m-d'),
            'principal_component' => 3000,
            'interest_component'  => 900,
            'principal_paid'      => 0,
            'interest_paid'       => 0,
            'opening_balance'     => 20000,
            'closing_balance'     => 17000,
            'status'              => 'pending',
        ]);

        $this->artisan('app:update-overdue-statuses')
            ->assertExitCode(0);

        $loan = Loan::find(TestingSeeder::LOAN_ACTIVE_ID);
        $this->assertEquals('overdue', $loan->status);
    }

    /** @test */
    public function overdue_transition_writes_status_history(): void
    {
        LoanSchedule::create([
            'loan_id'             => TestingSeeder::LOAN_ACTIVE_ID,
            'instalment_number'   => 99,
            'due_date'            => Carbon::now()->subDays(5)->format('Y-m-d'),
            'principal_component' => 3000,
            'interest_component'  => 900,
            'principal_paid'      => 0,
            'interest_paid'       => 0,
            'opening_balance'     => 20000,
            'closing_balance'     => 17000,
            'status'              => 'pending',
        ]);

        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        $this->assertDatabaseHas('loan_status_histories', [
            'loan_id'         => TestingSeeder::LOAN_ACTIVE_ID,
            'previous_status' => 'active',
            'new_status'      => 'overdue',
            'changed_by'      => null, // system-initiated
        ]);
    }

    /** @test */
    public function command_does_not_remark_already_overdue_loans(): void
    {
        // Run twice; second run should produce no new status history entries
        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        $countAfterFirst = LoanStatusHistory::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('new_status', 'overdue')
            ->count();

        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        $countAfterSecond = LoanStatusHistory::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)
            ->where('new_status', 'overdue')
            ->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }

    // ── Auto-close ────────────────────────────────────────────────────────────

    /** @test */
    public function command_auto_closes_loan_when_all_instalments_paid_and_balance_zero(): void
    {
        // Set all active loan's schedules to 'paid' and zero the balance
        LoanSchedule::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['status' => 'paid', 'principal_paid' => DB::raw('principal_component'),
                      'interest_paid' => DB::raw('interest_component')]);

        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_outstanding' => 0.00, 'penalty_balance' => 0.00]);

        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        $loan = Loan::find(TestingSeeder::LOAN_ACTIVE_ID);
        $this->assertEquals('closed', $loan->status);
    }

    /** @test */
    public function auto_close_releases_collateral(): void
    {
        LoanSchedule::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['status' => 'paid', 'principal_paid' => DB::raw('principal_component'),
                      'interest_paid' => DB::raw('interest_component')]);

        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_outstanding' => 0.00, 'penalty_balance' => 0.00]);

        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        // Loan 1 uses asset 1 (pledged vehicle)
        $this->assertDatabaseHas('collateral_assets', [
            'id'     => TestingSeeder::ASSET_VEHICLE_PLEDGED_ID,
            'status' => 'available',
        ]);
    }

    /** @test */
    public function auto_close_does_not_trigger_when_balance_outstanding(): void
    {
        // All schedules paid but balance still > 0 (e.g. un-reconciled penalty)
        LoanSchedule::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['status' => 'paid', 'principal_paid' => DB::raw('principal_component'),
                      'interest_paid' => DB::raw('interest_component')]);

        LoanBalance::where('loan_id', TestingSeeder::LOAN_ACTIVE_ID)
            ->update(['total_outstanding' => 500.00]); // still has balance

        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        $loan = Loan::find(TestingSeeder::LOAN_ACTIVE_ID);
        $this->assertNotEquals('closed', $loan->status);
    }

    // ── Dry run ───────────────────────────────────────────────────────────────

    /** @test */
    public function dry_run_shows_changes_without_writing(): void
    {
        LoanSchedule::create([
            'loan_id'             => TestingSeeder::LOAN_ACTIVE_ID,
            'instalment_number'   => 98,
            'due_date'            => Carbon::now()->subDays(5)->format('Y-m-d'),
            'principal_component' => 3000,
            'interest_component'  => 900,
            'principal_paid'      => 0,
            'interest_paid'       => 0,
            'opening_balance'     => 20000,
            'closing_balance'     => 17000,
            'status'              => 'pending',
        ]);

        $this->artisan('app:update-overdue-statuses --dry-run')
            ->expectsOutputToContain('DRY RUN')
            ->assertExitCode(0);

        // The loan should still be active — dry run wrote nothing
        $loan = Loan::find(TestingSeeder::LOAN_ACTIVE_ID);
        $this->assertEquals('active', $loan->status);
    }

    // ── Single-loan mode ──────────────────────────────────────────────────────

    /** @test */
    public function loan_option_restricts_command_to_single_loan(): void
    {
        // Both active and overdue loans have past-due schedules
        // but with --loan= only the specified loan should be processed
        $this->artisan('app:update-overdue-statuses --loan=' . TestingSeeder::LOAN_OVERDUE_ID)
            ->assertExitCode(0);

        // Loan 1 (active) should remain unchanged since we only processed loan 2
        $loan = Loan::find(TestingSeeder::LOAN_ACTIVE_ID);
        $this->assertEquals('active', $loan->status);
    }

    // ── Balance update ────────────────────────────────────────────────────────

    /** @test */
    public function command_updates_days_overdue_on_balance_record(): void
    {
        $this->artisan('app:update-overdue-statuses')->assertExitCode(0);

        $balance = LoanBalance::where('loan_id', TestingSeeder::LOAN_OVERDUE_ID)->first();
        $this->assertGreaterThan(0, $balance->days_overdue);
        $this->assertGreaterThan(0, $balance->instalments_overdue);
    }
}
<?php

// ═══════════════════════════════════════════════════════════════════════════════
// ReconcileCommandTest
// File: tests/Feature/Commands/ReconcileCommandTest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace Tests\Feature\Commands;
