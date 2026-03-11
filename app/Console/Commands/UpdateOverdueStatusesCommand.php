<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\LoanStatusHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateOverdueStatusesCommand extends Command
{
    protected $signature = 'app:update-overdue-statuses
        {--dry-run   : Show changes without writing to the database}
        {--loan=     : Restrict to a single loan ID}';

    protected $description = 'Mark overdue loan schedules and loans; auto-close fully-paid loans.';

    private bool $dryRun  = false;
    private array $counts = [
        'schedules_marked_overdue' => 0,
        'loans_marked_overdue'     => 0,
        'loans_auto_closed'        => 0,
        'balances_updated'         => 0,
        'errors'                   => 0,
    ];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $loanId       = $this->option('loan');

        if ($this->dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        $this->info('Starting overdue status update — ' . now()->format('Y-m-d H:i:s') . ' ZST');
        $start = microtime(true);

        $this->step1MarkOverdueSchedules($loanId);
        $this->step2MarkOverdueLoans($loanId);
        $this->step3AutoCloseFullyPaidLoans($loanId);
        $this->step4UpdateBalanceDaysOverdue($loanId);

        $elapsed = round(microtime(true) - $start, 2);

        $this->newLine();
        $this->table(
            ['Action', 'Count'],
            array_map(
                fn ($k, $v) => [str_replace('_', ' ', ucfirst($k)), $v],
                array_keys($this->counts),
                array_values($this->counts)
            )
        );
        $this->info("Completed in {$elapsed}s" . ($this->dryRun ? ' (dry run)' : ''));

        Log::info('[UpdateOverdueStatuses] Completed', array_merge($this->counts, ['elapsed_s' => $elapsed]));

        return self::SUCCESS;
    }

    // ── Step 1: Mark individual instalment rows as overdue ────────────────────

    private function step1MarkOverdueSchedules(?string $loanId): void
    {
        $this->line('  Step 1: Marking overdue instalment rows...');

        $query = LoanSchedule::query()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now()->startOfDay())
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']))
            ->with('loan:id,loan_number');

        if ($loanId) {
            $query->where('loan_id', $loanId);
        }

        $overdueSchedules = $query->get();

        foreach ($overdueSchedules as $schedule) {
            $this->counts['schedules_marked_overdue']++;

            if ($this->dryRun) {
                $this->line("    [DRY-RUN] Schedule #{$schedule->id} (loan {$schedule->loan->loan_number}, "
                    . "instalment {$schedule->instalment_number}, due {$schedule->due_date}) → overdue");
                continue;
            }

            try {
                $schedule->update(['status' => 'overdue']);
            } catch (\Throwable $e) {
                $this->error("    Failed to update schedule #{$schedule->id}: {$e->getMessage()}");
                $this->counts['errors']++;
            }
        }

        $this->line("    → {$this->counts['schedules_marked_overdue']} instalment rows marked overdue.");
    }

    // ── Step 2: Mark loans as overdue where any instalment is overdue ─────────

    private function step2MarkOverdueLoans(?string $loanId): void
    {
        $this->line('  Step 2: Marking loans as overdue...');

        $query = Loan::query()
            ->where('status', 'active')
            ->whereHas('loanSchedule', fn ($q) => $q->where('status', 'overdue'));

        if ($loanId) {
            $query->where('id', $loanId);
        }

        $loans = $query->get();

        foreach ($loans as $loan) {
            $this->counts['loans_marked_overdue']++;

            if ($this->dryRun) {
                $this->line("    [DRY-RUN] Loan {$loan->loan_number} → overdue");
                continue;
            }

            try {
                DB::transaction(function () use ($loan) {
                    $loan->update(['status' => 'overdue']);

                    LoanStatusHistory::create([
                        'loan_id'         => $loan->id,
                        'previous_status' => 'active',
                        'new_status'      => 'overdue',
                        'notes'           => 'Auto-marked overdue by system — one or more instalments past due.',
                        'changed_by'      => null, // system transition
                    ]);
                });
            } catch (\Throwable $e) {
                $this->error("    Failed to mark loan {$loan->loan_number} overdue: {$e->getMessage()}");
                $this->counts['errors']++;
            }
        }

        $this->line("    → {$this->counts['loans_marked_overdue']} loans marked overdue.");
    }

    // ── Step 3: Auto-close fully repaid loans ────────────────────────────────

    private function step3AutoCloseFullyPaidLoans(?string $loanId): void
    {
        $this->line('  Step 3: Auto-closing fully paid loans...');

        $query = Loan::query()
            ->whereIn('status', ['active', 'overdue'])
            ->whereDoesntHave('loanSchedule', fn ($q) =>
                $q->whereNotIn('status', ['paid'])
            )
            ->with('loanBalance:loan_id,total_outstanding');

        if ($loanId) {
            $query->where('id', $loanId);
        }

        $candidates = $query->get();

        foreach ($candidates as $loan) {
            // Guard: balance must also be zero (schedule rows might lag behind payments)
            $balance = $loan->loanBalance?->total_outstanding ?? null;
            if ($balance === null || $balance > 0.01) {
                continue;
            }

            $this->counts['loans_auto_closed']++;

            if ($this->dryRun) {
                $this->line("    [DRY-RUN] Loan {$loan->loan_number} → closed (all instalments paid, balance zero)");
                continue;
            }

            try {
                DB::transaction(function () use ($loan) {
                    $loan->update(['status' => 'closed']);

                    // Release collateral
                    if ($loan->collateral_asset_id) {
                        $loan->collateralAsset?->update(['status' => 'available']);
                    }

                    LoanStatusHistory::create([
                        'loan_id'         => $loan->id,
                        'previous_status' => $loan->getOriginal('status'),
                        'new_status'      => 'closed',
                        'notes'           => 'Auto-closed by system — all instalments paid and balance cleared.',
                        'changed_by'      => null,
                    ]);
                });
            } catch (\Throwable $e) {
                $this->error("    Failed to close loan {$loan->loan_number}: {$e->getMessage()}");
                $this->counts['errors']++;
            }
        }

        $this->line("    → {$this->counts['loans_auto_closed']} loans auto-closed.");
    }

    // ── Step 4: Update days_overdue on loan_balances ──────────────────────────

    private function step4UpdateBalanceDaysOverdue(?string $loanId): void
    {
        $this->line('  Step 4: Refreshing balance overdue counters...');

        $query = Loan::query()
            ->where('status', 'overdue')
            ->with(['loanBalance', 'loanSchedule' => fn ($q) =>
                $q->where('status', 'overdue')->orderBy('due_date')
            ]);

        if ($loanId) {
            $query->where('id', $loanId);
        }

        foreach ($query->cursor() as $loan) {
            $firstOverdue    = $loan->loanSchedule->first();
            $daysOverdue     = $firstOverdue
                ? max(0, (int) Carbon::parse($firstOverdue->due_date)->diffInDays(now()))
                : 0;
            $instalOverdue   = $loan->loanSchedule->count();
            $outstanding     = $loan->loanBalance?->total_outstanding ?? 0;
            $penaltyRate     = ($loan->loanProduct?->penalty_rate_percent ?? 2) / 100 / 30;
            $dailyAccrual    = round($outstanding * $penaltyRate, 2);

            if ($this->dryRun) {
                $this->counts['balances_updated']++;
                continue;
            }

            try {
                LoanBalance::where('loan_id', $loan->id)->update([
                    'days_overdue'          => $daysOverdue,
                    'instalments_overdue'   => $instalOverdue,
                    'daily_penalty_accrual' => $dailyAccrual,
                    'recalculated_at'       => now(),
                ]);
                $this->counts['balances_updated']++;
            } catch (\Throwable $e) {
                $this->counts['errors']++;
            }
        }

        $this->line("    → {$this->counts['balances_updated']} balances updated.");
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ApplyDailyPenaltiesCommand
// File: app/Console/Commands/ApplyDailyPenaltiesCommand.php
//
// Signature:  app:apply-daily-penalties
// Schedule:   Daily at 06:15 ZST (runs after UpdateOverdueStatusesCommand)
//
// What it does:
//   For every overdue loan schedule row that is past its grace period:
//   - Calculates the day's penalty amount (rate × base / 30)
//   - Creates a Penalty record tagged to the loan + schedule row
//   - Updates loan_balances.penalty_balance
//
// Options:
//   --dry-run        Show totals without writing
//   --loan=ID        Process single loan only
//   --date=YYYY-MM-DD  Backdate the penalty to a specific date (for corrections)
// ═══════════════════════════════════════════════════════════════════════════════
