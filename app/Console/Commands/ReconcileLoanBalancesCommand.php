<?php

namespace App\Console\Commands;

use App\Mail\DailyPortfolioReport;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\Penalty;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReconcileLoanBalancesCommand extends Command
{
    protected $signature = 'app:reconcile-loan-balances
        {--loan=      : Rebuild a single loan ID only}
        {--dry-run    : Show discrepancies without writing}
        {--mismatch   : Only report/update loans with balance discrepancies}';

    protected $description = 'Rebuild loan_balances from source payment and penalty records.';

    private array $stats = [
        'processed'   => 0,
        'updated'     => 0,
        'mismatches'  => 0,
        'errors'      => 0,
    ];

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $mismatch  = (bool) $this->option('mismatch');
        $loanId    = $this->option('loan');

        if ($dryRun) {
            $this->warn('DRY RUN — balances will be calculated but not written.');
        }

        $this->info('Reconciling loan balances — ' . now()->format('d M Y H:i:s'));
        $start = microtime(true);

        $query = Loan::query()
            ->whereNotNull('disbursed_at')
            ->whereIn('status', ['active', 'overdue', 'closed', 'written_off'])
            ->with([
                'loanBalance',
                'loanSchedule',
                'loanProduct:id,penalty_rate_percent,penalty_basis,grace_period_days',
            ]);

        if ($loanId) {
            $query->where('id', $loanId);
        }

        // Use cursor for memory efficiency — avoids loading all loans at once
        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        foreach ($query->cursor() as $loan) {
            $this->reconcileSingle($loan, $dryRun, $mismatch);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $elapsed = round(microtime(true) - $start, 2);
        $this->table(
            ['Metric', 'Value'],
            [
                ['Loans processed', $this->stats['processed']],
                ['Balances updated', $this->stats['updated']],
                ['Mismatches found', $this->stats['mismatches']],
                ['Errors',           $this->stats['errors']],
                ['Elapsed',          "{$elapsed}s"],
            ]
        );

        Log::info('[ReconcileLoanBalances] Completed', array_merge(
            $this->stats, ['elapsed_s' => $elapsed]
        ));

        return self::SUCCESS;
    }

    /**
     * Public so it can be called directly from Tinker and feature tests:
     *   app(ReconcileLoanBalancesCommand::class)->reconcileSingle(Loan::find(42));
     */
    public function reconcileSingle(Loan $loan, bool $dryRun = false, bool $mismatchOnly = false): void
    {
        $this->stats['processed']++;

        try {
            // ── Compute truth from source records ─────────────────────────────

            $totalPaid = Payment::where('loan_id', $loan->id)
                ->where('status', 'paid')
                ->sum('amount');

            $totalPenalties = Penalty::where('loan_id', $loan->id)
                ->where('status', 'outstanding')
                ->sum('amount');

            // Recompute principal and interest balances from schedule
            $schedules = $loan->loanSchedule ?? LoanSchedule::where('loan_id', $loan->id)->get();

            $principalBalance = $schedules->sum(fn ($s) =>
                max(0, $s->principal_component - $s->principal_paid)
            );
            $interestBalance = $schedules->sum(fn ($s) =>
                max(0, $s->interest_component - $s->interest_paid)
            );

            if ($loan->status === 'closed') {
                $principalBalance = 0;
                $interestBalance  = 0;
                $totalPenalties   = 0;
            }

            $totalOutstanding = $principalBalance + $interestBalance + $totalPenalties;

            // Days overdue and instalment counts
            $daysOverdue     = 0;
            $instalOverdue   = 0;
            $dailyAccrual    = 0;

            if ($loan->status === 'overdue') {
                $overdueSchedules = $schedules->where('status', 'overdue')->sortBy('due_date');
                $firstOverdue     = $overdueSchedules->first();
                $daysOverdue      = $firstOverdue
                    ? max(0, (int) Carbon::parse($firstOverdue->due_date)->diffInDays(now()))
                    : 0;
                $instalOverdue    = $overdueSchedules->count();
                $rate             = ($loan->loanProduct?->penalty_rate_percent ?? 2) / 100 / 30;
                $dailyAccrual     = round($totalOutstanding * $rate, 2);
            }

            $lastPayment = Payment::where('loan_id', $loan->id)
                ->where('status', 'paid')
                ->orderByDesc('payment_date')
                ->first();

            $instalRemaining = $schedules->whereIn('status', ['pending', 'overdue', 'partial'])->count();

            $computed = [
                'principal_balance'     => round($principalBalance, 2),
                'interest_balance'      => round($interestBalance, 2),
                'penalty_balance'       => round($totalPenalties, 2),
                'total_outstanding'     => round($totalOutstanding, 2),
                'total_paid'            => round($totalPaid, 2),
                'days_overdue'          => $daysOverdue,
                'instalments_overdue'   => $instalOverdue,
                'instalments_remaining' => $instalRemaining,
                'daily_penalty_accrual' => $dailyAccrual,
                'last_payment_date'     => $lastPayment?->payment_date,
                'last_payment_amount'   => $lastPayment?->amount,
                'recalculated_at'       => now(),
            ];

            // ── Detect mismatch ───────────────────────────────────────────────
            $existing = $loan->loanBalance;

            $hasMismatch = !$existing
                || abs(($existing->total_outstanding ?? 0) - $totalOutstanding) > 0.01
                || abs(($existing->total_paid ?? 0) - $totalPaid) > 0.01
                || abs(($existing->penalty_balance ?? 0) - $totalPenalties) > 0.01;

            if ($hasMismatch) {
                $this->stats['mismatches']++;

                if ($dryRun || $mismatchOnly) {
                    $this->warn(sprintf(
                        '    Mismatch on loan %s — stored: K%s outstanding / K%s paid | computed: K%s outstanding / K%s paid',
                        $loan->loan_number,
                        number_format($existing?->total_outstanding ?? 0, 2),
                        number_format($existing?->total_paid ?? 0, 2),
                        number_format($totalOutstanding, 2),
                        number_format($totalPaid, 2)
                    ));

                    if ($dryRun) {
                        return;
                    }
                }
            } elseif ($mismatchOnly) {
                // No mismatch and --mismatch flag set — skip this loan
                return;
            }

            // ── Write updated balance ────────────────────────────────────────
            LoanBalance::updateOrCreate(
                ['loan_id' => $loan->id],
                $computed
            );

            $this->stats['updated']++;

        } catch (\Throwable $e) {
            $this->stats['errors']++;
            Log::error('[ReconcileLoanBalances] Failed on loan ' . $loan->loan_number, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
