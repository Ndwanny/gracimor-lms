<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\Reminder;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplyDailyPenaltiesCommand extends Command
{
    protected $signature = 'app:apply-daily-penalties
        {--dry-run       : Calculate penalties without persisting}
        {--loan=         : Restrict to a single loan ID}
        {--date=         : Apply penalties as of this date (YYYY-MM-DD). Default: today}';

    protected $description = 'Calculate and persist daily penalty accruals for all overdue loans.';

    private array $totals = [
        'loans_processed'  => 0,
        'penalties_created'=> 0,
        'total_amount'     => 0.00,
        'skipped_grace'    => 0,
        'errors'           => 0,
    ];

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $loanId  = $this->option('loan');
        $asOfDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        if ($dryRun) {
            $this->warn('DRY RUN — no penalty records will be created.');
        }

        $this->info("Applying daily penalties as of {$asOfDate->format('d M Y')}...");
        $start = microtime(true);

        // Load all overdue loans with needed relations
        $query = Loan::query()
            ->where('status', 'overdue')
            ->with([
                'loanProduct:id,penalty_rate_percent,penalty_basis,grace_period_days',
                'loanSchedule' => fn ($q) => $q->where('status', 'overdue'),
                'penalties'    => fn ($q) => $q->where('status', 'outstanding')
                                              ->whereDate('applied_at', $asOfDate->format('Y-m-d')),
            ]);

        if ($loanId) {
            $query->where('id', $loanId);
        }

        $loans = $query->get();

        $this->withProgressBar($loans, function (Loan $loan) use ($dryRun, $asOfDate) {
            $this->processLoan($loan, $dryRun, $asOfDate);
        });

        $this->newLine(2);

        $elapsed = round(microtime(true) - $start, 2);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Loans processed',   $this->totals['loans_processed']],
                ['Penalties created', $this->totals['penalties_created']],
                ['Total amount',      'K ' . number_format($this->totals['total_amount'], 2)],
                ['Skipped (in grace)',  $this->totals['skipped_grace']],
                ['Errors',            $this->totals['errors']],
                ['Elapsed',           "{$elapsed}s"],
            ]
        );

        if ($dryRun) {
            $this->warn('(Dry run — nothing was written)');
        }

        Log::info('[ApplyDailyPenalties] Completed', array_merge(
            $this->totals, ['as_of_date' => $asOfDate->format('Y-m-d'), 'elapsed_s' => $elapsed]
        ));

        return self::SUCCESS;
    }

    private function processLoan(Loan $loan, bool $dryRun, Carbon $asOfDate): void
    {
        $product     = $loan->loanProduct;
        $graceDays   = $product?->grace_period_days ?? 7;
        $rate        = ($product?->penalty_rate_percent ?? 2.00) / 100;
        $basis       = $product?->penalty_basis ?? 'instalment';

        $this->totals['loans_processed']++;

        foreach ($loan->loanSchedule as $schedule) {
            $daysOverdue    = max(0, Carbon::parse($schedule->due_date)->diffInDays($asOfDate));
            $daysAfterGrace = max(0, $daysOverdue - $graceDays);

            if ($daysAfterGrace <= 0) {
                $this->totals['skipped_grace']++;
                continue;
            }

            // Check: penalty already applied today for this schedule
            $alreadyApplied = $loan->penalties->contains(
                fn ($p) => $p->loan_schedule_id === $schedule->id
            );

            if ($alreadyApplied) {
                continue;
            }

            // Calculate base amount
            $baseAmount = $basis === 'instalment'
                ? ($schedule->principal_component + $schedule->interest_component
                   - $schedule->principal_paid - $schedule->interest_paid)
                : $loan->loanBalance?->principal_balance ?? $loan->principal_amount;

            $penaltyAmount = round($baseAmount * $rate, 2);

            if ($penaltyAmount < 1.00) {
                continue; // Skip sub-ZMW penny amounts
            }

            $this->totals['penalties_created']++;
            $this->totals['total_amount'] += $penaltyAmount;

            if ($dryRun) {
                continue;
            }

            try {
                DB::transaction(function () use ($loan, $schedule, $penaltyAmount, $rate, $basis, $daysOverdue, $daysAfterGrace, $asOfDate) {
                    \App\Models\Penalty::create([
                        'loan_id'          => $loan->id,
                        'loan_schedule_id' => $schedule->id,
                        'amount'           => $penaltyAmount,
                        'rate_applied'     => $rate * 100,
                        'basis'            => $basis,
                        'days_overdue'     => $daysOverdue,
                        'days_after_grace' => $daysAfterGrace,
                        'status'           => 'outstanding',
                        'applied_at'       => $asOfDate,
                    ]);

                    // Increment penalty_balance on the loan balance
                    LoanBalance::where('loan_id', $loan->id)->increment(
                        'penalty_balance', $penaltyAmount
                    );
                    LoanBalance::where('loan_id', $loan->id)->increment(
                        'total_outstanding', $penaltyAmount
                    );
                    LoanBalance::where('loan_id', $loan->id)->update([
                        'recalculated_at' => now(),
                    ]);
                });
            } catch (\Throwable $e) {
                $this->totals['errors']++;
                Log::error('[ApplyDailyPenalties] Failed on schedule #' . $schedule->id, [
                    'loan'  => $loan->loan_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
