<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RecalculateLoanBalancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 600;

    public function __construct(
        /** Pass specific loan IDs to recalculate only a subset. Null = all active loans. */
        public readonly ?array $loanIds = null,
    ) {}

    public function handle(): void
    {
        $start = microtime(true);
        Log::info("[RecalculateLoanBalancesJob] Starting" .
            ($this->loanIds ? " — " . count($this->loanIds) . " specific loans" : " — all active"));

        $query = Loan::whereIn('status', ['active', 'overdue'])
            ->with([
                'loanBalance',
                'loanSchedule',
                'payments.paymentAllocations',
                'penalties',
            ]);

        if ($this->loanIds) {
            $query->whereIn('id', $this->loanIds);
        }

        $loans    = $query->cursor(); // cursor() for memory efficiency on large sets
        $updated  = 0;
        $mismatch = 0;

        foreach ($loans as $loan) {
            $computed = $this->computeBalance($loan);

            $balance = $loan->loanBalance ?? LoanBalance::make(['loan_id' => $loan->id]);

            // Only write if something has changed (avoid unnecessary writes)
            if (
                abs($balance->principal_balance - $computed['principal_balance']) > 0.005 ||
                abs($balance->interest_balance  - $computed['interest_balance'])  > 0.005 ||
                abs($balance->penalty_balance   - $computed['penalty_balance'])   > 0.005
            ) {
                $mismatch++;
                Log::warning("[RecalculateLoanBalancesJob] Mismatch on loan {$loan->loan_number}", [
                    'stored_principal'  => $balance->principal_balance,
                    'computed_principal'=> $computed['principal_balance'],
                ]);
            }

            LoanBalance::updateOrCreate(
                ['loan_id' => $loan->id],
                $computed,
            );

            $updated++;
        }

        $elapsed = round(microtime(true) - $start, 3);
        Log::info("[RecalculateLoanBalancesJob] Completed in {$elapsed}s — " .
            "loans processed: {$updated}, mismatches corrected: {$mismatch}");
    }

    private function computeBalance(Loan $loan): array
    {
        $schedule = $loan->loanSchedule;

        // Total principal that should have been paid by now (past-due instalments only)
        $principalScheduled = $schedule
            ->whereIn('status', ['paid', 'overdue', 'partial'])
            ->sum('principal_component');
        $principalPaid = $schedule->sum('principal_paid');
        $principalBalance = max(0, $loan->principal_amount - $principalPaid);

        $interestScheduled = $schedule->sum('interest_component');
        $interestPaid      = $schedule->sum('interest_paid');
        $interestBalance   = max(0, $interestScheduled - $interestPaid);

        $penaltyTotal    = $loan->penalties->where('status', 'outstanding')->sum('amount');
        $penaltyPaid     = $loan->penalties->where('status', 'paid')->sum('amount');
        $penaltyBalance  = max(0, $penaltyTotal);

        $totalOutstanding = $principalBalance + $interestBalance + $penaltyBalance;

        // Days overdue: max days past due date for any overdue instalment
        $maxDaysOverdue = $schedule
            ->whereIn('status', ['overdue', 'partial'])
            ->where('due_date', '<', today())
            ->max(fn ($s) => today()->diffInDays($s->due_date));

        return [
            'loan_id'                => $loan->id,
            'principal_balance'      => round($principalBalance, 2),
            'interest_balance'       => round($interestBalance, 2),
            'penalty_balance'        => round($penaltyBalance, 2),
            'total_outstanding'      => round($totalOutstanding, 2),
            'total_paid'             => round($loan->payments->sum('amount'), 2),
            'days_overdue'           => (int) ($maxDaysOverdue ?? 0),
            'instalments_overdue'    => $schedule->whereIn('status', ['overdue', 'partial'])->count(),
            'instalments_remaining'  => $schedule->where('status', 'pending')->count(),
            'last_payment_date'      => $loan->payments->sortByDesc('payment_date')->first()?->payment_date,
            'last_payment_amount'    => $loan->payments->sortByDesc('payment_date')->first()?->amount,
            'recalculated_at'        => now(),
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[RecalculateLoanBalancesJob] FAILED: " . $exception->getMessage());
    }
}
