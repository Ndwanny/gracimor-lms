<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * PenaltyService
 *
 * Handles daily automated penalty application (called by ApplyPenaltiesJob)
 * and manual officer actions (waive, manually apply).
 */
class PenaltyService
{
    // ── Daily run (called by scheduled job) ───────────────────────────────

    /**
     * Apply penalties to all overdue instalments across all active loans.
     *
     * Logic per schedule row:
     *  - status must be 'overdue'
     *  - days_overdue must exceed the product's grace_period_days
     *  - Only one penalty per row per day (guard against duplicate job runs)
     *
     * @param  Carbon|null $asOf  Defaults to today. Override for backfill.
     * @return int  Number of penalties applied
     */
    public function applyDailyPenalties(?Carbon $asOf = null): int
    {
        $asOf  = $asOf ?? Carbon::today();
        $count = 0;

        // Load all overdue rows with their loan product (for rate + grace period)
        $overdueRows = LoanSchedule::where('status', 'overdue')
            ->with(['loan.product', 'loan.borrower'])
            ->get();

        foreach ($overdueRows as $row) {
            $product     = $row->loan->product;
            $daysOverdue = (int) $asOf->diffInDays(Carbon::parse($row->due_date));

            // Respect grace period
            if ($daysOverdue <= $product->grace_period_days) {
                continue;
            }

            // Guard: do not double-apply on the same day
            $alreadyApplied = Penalty::where('loan_schedule_id', $row->id)
                ->whereDate('applied_date', $asOf->toDateString())
                ->exists();

            if ($alreadyApplied) {
                continue;
            }

            DB::transaction(function () use ($row, $product, $asOf, $daysOverdue, &$count) {
                $penaltyAmount = $product->calculatePenalty((float) $row->total_due);

                // Create penalty record
                Penalty::create([
                    'loan_id'                      => $row->loan_id,
                    'loan_schedule_id'              => $row->id,
                    'borrower_id'                  => $row->loan->borrower_id,
                    'penalty_amount'               => $penaltyAmount,
                    'penalty_rate_used'            => $product->penalty_rate_percent,
                    'applied_date'                 => $asOf->toDateString(),
                    'days_overdue_at_application'  => $daysOverdue,
                    'status'                       => 'outstanding',
                    'is_system_generated'          => true,
                ]);

                // Accumulate on the schedule row
                $row->increment('penalty_amount', $penaltyAmount);
                $row->update([
                    'days_overdue'      => $daysOverdue,
                    'penalty_applied_at'=> $asOf->toDateString(),
                ]);

                // Reflect on loan_balances
                LoanBalance::where('loan_id', $row->loan_id)
                    ->increment('penalty_charged', $penaltyAmount);

                LoanBalance::where('loan_id', $row->loan_id)
                    ->increment('penalty_outstanding', $penaltyAmount);

                LoanBalance::where('loan_id', $row->loan_id)
                    ->increment('total_outstanding', $penaltyAmount);

                $count++;
            });
        }

        return $count;
    }

    // ── Update overdue statuses ───────────────────────────────────────────

    /**
     * Transition schedule rows from 'due' / 'partial' to 'overdue'
     * for any rows whose due_date has passed today.
     *
     * Also updates the days_overdue counter on existing overdue rows.
     * Called nightly before applyDailyPenalties.
     *
     * @return int  Number of rows updated
     */
    public function updateOverdueStatuses(?Carbon $asOf = null): int
    {
        $asOf = $asOf ?? Carbon::today();

        // Transition due/partial → overdue
        $updated = LoanSchedule::whereIn('status', ['due', 'partial'])
            ->where('due_date', '<', $asOf->toDateString())
            ->update([
                'status'     => 'overdue',
                'updated_at' => now(),
            ]);

        // Refresh days_overdue counter on existing overdue rows
        LoanSchedule::where('status', 'overdue')
            ->get(['id', 'due_date'])
            ->each(function ($row) use ($asOf) {
                $days = (int) $asOf->diffInDays(Carbon::parse($row->due_date));
                LoanSchedule::where('id', $row->id)->update(['days_overdue' => $days]);
            });

        // Sync instalments_overdue on loan_balances
        $this->syncOverdueCounts();

        return $updated;
    }

    /**
     * Re-sync the instalments_overdue counter on all affected loan_balance rows.
     */
    private function syncOverdueCounts(): void
    {
        DB::statement("
            UPDATE loan_balances lb
            JOIN (
                SELECT loan_id, COUNT(*) AS overdue_count
                FROM loan_schedule
                WHERE status = 'overdue'
                GROUP BY loan_id
            ) counts ON lb.loan_id = counts.loan_id
            SET lb.instalments_overdue = counts.overdue_count
        ");
    }

    // ── Manual waiver ─────────────────────────────────────────────────────

    /**
     * Waive all outstanding penalties on a specific schedule row.
     *
     * @param  LoanSchedule  $row
     * @param  User          $by
     * @param  string        $reason
     * @return int  Number of penalties waived
     */
    public function waivePenaltiesOnRow(LoanSchedule $row, User $by, string $reason): int
    {
        $outstanding = Penalty::where('loan_schedule_id', $row->id)
            ->where('status', 'outstanding')
            ->get();

        if ($outstanding->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($outstanding, $row, $by, $reason) {
            $totalWaived = 0;

            foreach ($outstanding as $penalty) {
                $penalty->update([
                    'status'        => 'waived',
                    'waived_by'     => $by->id,
                    'waiver_reason' => $reason,
                    'waived_at'     => now(),
                ]);
                $totalWaived += (float) $penalty->penalty_amount;
            }

            // Reduce the schedule row's penalty_amount by the waived total
            $row->decrement('penalty_amount', $totalWaived);

            // Reduce loan balance
            LoanBalance::where('loan_id', $row->loan_id)
                ->decrement('penalty_charged',    $totalWaived);
            LoanBalance::where('loan_id', $row->loan_id)
                ->decrement('penalty_outstanding', $totalWaived);
            LoanBalance::where('loan_id', $row->loan_id)
                ->decrement('total_outstanding',   $totalWaived);

            AuditLog::record('penalty.waived', $row, [], [
                'total_waived' => $totalWaived,
                'reason'       => $reason,
            ]);

            return count($outstanding);
        });
    }

    /**
     * Waive all outstanding penalties across an entire loan.
     */
    public function waiveAllOnLoan(Loan $loan, User $by, string $reason): int
    {
        $count = 0;

        foreach ($loan->schedule as $row) {
            $count += $this->waivePenaltiesOnRow($row, $by, $reason);
        }

        return $count;
    }

    /**
     * Waive penalties for a single loan — called by PenaltyController.
     * scope = 'all' waives every outstanding penalty on the loan.
     * scope = 'instalment' waives penalties on one specific schedule row.
     *
     * Returns ['count' => int, 'amount' => float, 'remaining' => float]
     */
    public function waive(
        int $loanId,
        string $scope,
        ?int $scheduleId,
        string $reason,
        ?string $notes,
        int $userId
    ): array {
        $loan = Loan::with(['loanSchedule', 'loanBalance'])->findOrFail($loanId);
        $by   = User::findOrFail($userId);

        return DB::transaction(function () use ($loan, $scope, $scheduleId, $reason, $notes, $by) {
            if ($scope === 'instalment' && $scheduleId) {
                $row   = LoanSchedule::findOrFail($scheduleId);
                $count = $this->waivePenaltiesOnRow($row, $by, $reason);
            } else {
                $count = $this->waiveAllOnLoan($loan, $by, $reason);
            }

            $amountWaived = $count > 0
                ? (float) Penalty::where('loan_id', $loan->id)
                    ->where('status', 'waived')
                    ->sum('penalty_amount')
                : 0;

            $remaining = (float) Penalty::where('loan_id', $loan->id)
                ->where('status', 'outstanding')
                ->sum('penalty_amount');

            return ['count' => $count, 'amount' => $amountWaived, 'remaining' => $remaining];
        });
    }

    /**
     * Waive all outstanding penalties across multiple loans — bulk action.
     *
     * Returns ['loans_affected' => int, 'count' => int, 'amount' => float]
     */
    public function bulkWaive(
        array $loanIds,
        string $reason,
        ?string $notes,
        int $userId
    ): array {
        $by    = User::findOrFail($userId);
        $loans = Loan::with('loanSchedule')->whereIn('id', $loanIds)->get();

        $totalCount    = 0;
        $totalAmount   = 0;
        $loansAffected = 0;

        DB::transaction(function () use ($loans, $by, $reason, &$totalCount, &$totalAmount, &$loansAffected) {
            foreach ($loans as $loan) {
                $before = (float) Penalty::where('loan_id', $loan->id)
                    ->where('status', 'outstanding')
                    ->sum('penalty_amount');

                $count = $this->waiveAllOnLoan($loan, $by, $reason);

                if ($count > 0) {
                    $totalCount  += $count;
                    $totalAmount += $before;
                    $loansAffected++;
                }
            }
        });

        return ['loans_affected' => $loansAffected, 'count' => $totalCount, 'amount' => $totalAmount];
    }

    /**
     * Validate a manager authorisation code for bulk waiver.
     * Accepts the literal string "CONFIRM" (development) or any non-empty string
     * when the user has manager/superadmin role (production uplift: replace with PIN check).
     */
    public function validateAuthCode(string $code, User $user): bool
    {
        if (trim($code) === '') {
            return false;
        }
        // Managers and superadmins may confirm with "CONFIRM" or their own 6-digit PIN
        if (in_array($user->role, ['manager', 'superadmin', 'ceo'])) {
            return strtoupper(trim($code)) === 'CONFIRM' || (strlen($code) === 6 && ctype_digit($code));
        }
        return false;
    }
}
