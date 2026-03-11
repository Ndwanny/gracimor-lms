<?php

namespace App\Jobs;

use App\Events\PenaltyApplied;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\Penalty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateOverdueStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300; // 5 minutes

    public function __construct(
        public readonly ?string $asOfDate = null,
    ) {}

    public function handle(): void
    {
        $asOf = $this->asOfDate ?? today()->toDateString();

        Log::info("[UpdateOverdueStatusesJob] Starting — as_of: {$asOf}");
        $start = microtime(true);

        DB::transaction(function () use ($asOf) {

            // ── Step 1: Mark individual instalments as overdue ──────────────
            $markedOverdue = LoanSchedule::query()
                ->whereIn('status', ['pending', 'partial'])
                ->whereDate('due_date', '<', $asOf)
                ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']))
                ->update(['status' => 'overdue']);

            Log::info("[UpdateOverdueStatusesJob] Instalments marked overdue: {$markedOverdue}");

            // ── Step 2: Mark partial instalments overdue (partially paid but past due) ─
            // Already handled above — 'partial' is included in Step 1.

            // ── Step 3: Transition loans active → overdue ────────────────────
            $loansToMakeOverdue = Loan::active()
                ->whereHas('loanSchedule', fn ($q) => $q->where('status', 'overdue'))
                ->get();

            $newlyOverdueIds = [];
            foreach ($loansToMakeOverdue as $loan) {
                $previousStatus = $loan->status;
                $loan->update(['status' => 'overdue']);

                $loan->statusHistory()->create([
                    'previous_status' => $previousStatus,
                    'new_status'      => 'overdue',
                    'notes'           => "Auto-transitioned by UpdateOverdueStatusesJob on {$asOf}.",
                    'changed_by'      => null, // system action
                ]);

                $newlyOverdueIds[] = $loan->id;
            }

            Log::info("[UpdateOverdueStatusesJob] Loans newly overdue: " . count($newlyOverdueIds));

            // ── Step 4: Recover overdue → active (all instalments now current) ──
            $recoveredLoans = Loan::overdue()
                ->whereDoesntHave('loanSchedule', fn ($q) => $q->where('status', 'overdue'))
                ->get();

            foreach ($recoveredLoans as $loan) {
                $loan->update(['status' => 'active']);

                $loan->statusHistory()->create([
                    'previous_status' => 'overdue',
                    'new_status'      => 'active',
                    'notes'           => "Recovered to active — all overdue instalments now cleared on {$asOf}.",
                    'changed_by'      => null,
                ]);
            }

            Log::info("[UpdateOverdueStatusesJob] Loans recovered to active: {$recoveredLoans->count()}");

            // ── Step 5: Refresh days_overdue on all active LoanBalance rows ──
            // Uses a raw UPDATE to set days_overdue = max days of any overdue instalment
            DB::statement("
                UPDATE loan_balances lb
                JOIN (
                    SELECT
                        loans.id AS loan_id,
                        MAX(DATEDIFF('{$asOf}', ls.due_date)) AS max_days_overdue
                    FROM loans
                    JOIN loan_schedules ls ON ls.loan_id = loans.id
                    WHERE loans.status IN ('active', 'overdue')
                      AND ls.status IN ('overdue', 'partial')
                      AND ls.due_date < '{$asOf}'
                    GROUP BY loans.id
                ) calc ON lb.loan_id = calc.loan_id
                SET lb.days_overdue = calc.max_days_overdue
                WHERE calc.max_days_overdue IS NOT NULL
            ");

            // Reset days_overdue to 0 for loans with no overdue instalments
            DB::statement("
                UPDATE loan_balances lb
                JOIN loans ON loans.id = lb.loan_id
                WHERE loans.status = 'active'
                  AND NOT EXISTS (
                      SELECT 1 FROM loan_schedules ls
                      WHERE ls.loan_id = lb.loan_id
                        AND ls.status IN ('overdue', 'partial')
                        AND ls.due_date < '{$asOf}'
                  )
                SET lb.days_overdue = 0
            ");

            // ── Step 6: Recalculate daily_penalty_accrual on each balance ───
            // For display only — actual penalty records are created by ApplyDailyPenaltiesJob
            DB::statement("
                UPDATE loan_balances lb
                JOIN loans l ON l.id = lb.loan_id
                JOIN loan_products lp ON lp.id = l.loan_product_id
                SET lb.daily_penalty_accrual = CASE
                    WHEN lp.penalty_basis = 'instalment' THEN
                        (SELECT COALESCE(SUM(
                            (ls.principal_component - ls.principal_paid +
                             ls.interest_component  - ls.interest_paid)
                            * lp.penalty_rate_percent / 100
                        ), 0)
                        FROM loan_schedules ls
                        WHERE ls.loan_id = l.id
                          AND ls.status IN ('overdue', 'partial'))
                    ELSE
                        lb.total_outstanding * lp.penalty_rate_percent / 100
                END
                WHERE l.status = 'overdue'
            ");

            // ── Step 7: Auto-close loans where balance = 0 ──────────────────
            $paidOffLoans = Loan::whereIn('status', ['active', 'overdue'])
                ->whereHas('loanBalance', fn ($q) => $q->where('total_outstanding', '<=', 0))
                ->get();

            foreach ($paidOffLoans as $loan) {
                $loan->update(['status' => 'closed']);

                $loan->statusHistory()->create([
                    'previous_status' => $loan->getOriginal('status'),
                    'new_status'      => 'closed',
                    'notes'           => "Auto-closed — outstanding balance reached zero on {$asOf}.",
                    'changed_by'      => null,
                ]);
            }

            Log::info("[UpdateOverdueStatusesJob] Loans auto-closed: {$paidOffLoans->count()}");
        });

        // ── Step 8: Fire events for newly overdue loans (outside transaction) ──
        // Re-fetch to ensure we have the latest model state
        Loan::whereIn('id', $this->getNewlyOverdueLoanIds($asOf))
            ->with(['borrower', 'loanBalance', 'appliedBy'])
            ->each(fn ($loan) => event(new LoanOverdue($loan)));

        $elapsed = round(microtime(true) - $start, 3);
        Log::info("[UpdateOverdueStatusesJob] Completed in {$elapsed}s");
    }

    /**
     * Re-query loans that became overdue in this run (status = overdue AND
     * their latest status_history entry was created today by system).
     */
    private function getNewlyOverdueLoanIds(string $asOf): array
    {
        return Loan::overdue()
            ->whereHas('statusHistory', function ($q) use ($asOf) {
                $q->where('new_status', 'overdue')
                  ->whereNull('changed_by')
                  ->whereDate('created_at', $asOf);
            })
            ->pluck('id')
            ->toArray();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[UpdateOverdueStatusesJob] FAILED: " . $exception->getMessage(), [
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
