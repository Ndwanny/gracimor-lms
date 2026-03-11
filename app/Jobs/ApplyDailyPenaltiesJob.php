<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplyDailyPenaltiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly ?string $asOfDate = null,
    ) {}

    public function handle(): void
    {
        $asOf  = $this->asOfDate ?? today()->toDateString();
        $start = microtime(true);

        Log::info("[ApplyDailyPenaltiesJob] Starting — as_of: {$asOf}");

        // Fetch all overdue instalments with their loan product grace period info
        $overdueSchedules = LoanSchedule::query()
            ->whereIn('status', ['overdue', 'partial'])
            ->whereDate('due_date', '<', $asOf)
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['overdue', 'active']))
            ->with([
                'loan.loanProduct:id,penalty_rate_percent,penalty_basis,grace_period_days',
                'loan.loanBalance',
            ])
            ->get();

        Log::info("[ApplyDailyPenaltiesJob] Overdue instalments found: {$overdueSchedules->count()}");

        $penaltiesCreated = 0;
        $totalAmount      = 0.0;
        $loanIdsAffected  = [];

        foreach ($overdueSchedules as $schedule) {
            $loan    = $schedule->loan;
            $product = $loan->loanProduct;

            // Calculate how many days overdue (past the grace period)
            $daysOverdue    = now()->parse($asOf)->diffInDays($schedule->due_date);
            $daysAfterGrace = $daysOverdue - $product->grace_period_days;

            if ($daysAfterGrace <= 0) {
                // Still within grace period — skip
                continue;
            }

            // Idempotency check: has a penalty already been applied today for this instalment?
            $alreadyApplied = Penalty::where('loan_id', $loan->id)
                ->where('loan_schedule_id', $schedule->id)
                ->whereDate('applied_at', $asOf)
                ->exists();

            if ($alreadyApplied) {
                continue;
            }

            // Calculate penalty amount based on product configuration
            $penaltyAmount = $this->calculatePenalty($schedule, $loan, $product);

            if ($penaltyAmount <= 0) {
                continue;
            }

            // Create the Penalty record
            $penalty = Penalty::create([
                'loan_id'          => $loan->id,
                'loan_schedule_id' => $schedule->id,
                'amount'           => round($penaltyAmount, 2),
                'rate_applied'     => $product->penalty_rate_percent,
                'basis'            => $product->penalty_basis,
                'days_overdue'     => $daysOverdue,
                'days_after_grace' => $daysAfterGrace,
                'status'           => 'outstanding',
                'applied_at'       => now()->parse($asOf),
                'applied_by'       => null, // system
                'notes'            => "Auto-applied: {$daysOverdue} days overdue, {$daysAfterGrace}d past grace.",
            ]);

            $penaltiesCreated++;
            $totalAmount      += $penaltyAmount;
            $loanIdsAffected[] = $loan->id;

            // Fire event (for audit log, no SMS — SMS is handled by ReminderJob)
            event(new PenaltyApplied($penalty, $loan));
        }

        // Bulk-update LoanBalance.penalty_balance for all affected loans
        if (!empty($loanIdsAffected)) {
            $this->rebuildPenaltyBalances(array_unique($loanIdsAffected));
        }

        $elapsed = round(microtime(true) - $start, 3);

        Log::info("[ApplyDailyPenaltiesJob] Completed in {$elapsed}s — " .
            "penalties created: {$penaltiesCreated}, total amount: K " . number_format($totalAmount, 2));
    }

    /**
     * Calculate the penalty amount for a given overdue schedule entry.
     */
    private function calculatePenalty(
        LoanSchedule $schedule,
        Loan $loan,
        \App\Models\LoanProduct $product,
    ): float {
        if ($product->penalty_basis === 'instalment') {
            // Penalty on the remaining overdue amount of this instalment
            $overdueAmount = (
                ($schedule->principal_component - $schedule->principal_paid) +
                ($schedule->interest_component  - $schedule->interest_paid)
            );

            return $overdueAmount * ($product->penalty_rate_percent / 100);
        }

        // penalty_basis === 'outstanding_balance'
        $outstanding = $loan->loanBalance?->total_outstanding ?? 0;

        return $outstanding * ($product->penalty_rate_percent / 100);
    }

    /**
     * Recalculate and update the penalty_balance column on loan_balances
     * for all affected loans in one SQL statement.
     */
    private function rebuildPenaltyBalances(array $loanIds): void
    {
        $placeholders = implode(',', array_fill(0, count($loanIds), '?'));

        DB::statement("
            UPDATE loan_balances lb
            JOIN (
                SELECT loan_id, SUM(amount) AS penalty_total
                FROM penalties
                WHERE status = 'outstanding'
                  AND loan_id IN ({$placeholders})
                GROUP BY loan_id
            ) agg ON agg.loan_id = lb.loan_id
            SET lb.penalty_balance    = agg.penalty_total,
                lb.total_outstanding  = lb.principal_balance + lb.interest_balance + agg.penalty_total
            WHERE lb.loan_id IN ({$placeholders})
        ", [...$loanIds, ...$loanIds]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[ApplyDailyPenaltiesJob] FAILED: " . $exception->getMessage(), [
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
