<?php

namespace App\Console\Commands;

use App\Models\LoanSchedule;
use App\Models\Reminder;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInstalmentRemindersCommand extends Command
{
    protected $signature = 'app:send-instalment-reminders
        {--dry-run       : Render messages without sending SMS}
        {--type=         : Filter to "pre_due" or "overdue" only}
        {--loan=         : Restrict to a single loan ID}
        {--days=         : Override day-trigger (7, 3, 1, 0 for due-today)}';

    protected $description = 'Send pre-due and overdue instalment reminder SMS messages.';

    // Map days-before-or-after → trigger key
    private const PRE_DUE_TRIGGERS = [
        7 => 'pre_due_7_days',
        3 => 'pre_due_3_days',
        1 => 'pre_due_1_day',
        0 => 'due_today',
    ];

    private const OVERDUE_TRIGGERS = [
        1  => 'overdue_1_day',
        7  => 'overdue_7_days',
        14 => 'overdue_14_days',
        30 => 'overdue_30_days',
    ];

    private array $stats = [
        'sent'     => 0,
        'failed'   => 0,
        'skipped'  => 0,
        'dry_run'  => 0,
    ];

    public function __construct(private readonly ReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $type    = $this->option('type');    // 'pre_due' | 'overdue' | null
        $loanId  = $this->option('loan');
        $daysOpt = $this->option('days') !== null ? (int) $this->option('days') : null;

        if ($dryRun) {
            $this->warn('DRY RUN — SMS will be rendered but not sent.');
        }

        $this->info('Sending instalment reminders — ' . now()->format('d M Y H:i') . ' ZST');
        $start = microtime(true);

        // ── Pre-due reminders ─────────────────────────────────────────────────
        if (!$type || $type === 'pre_due') {
            $triggers = $daysOpt !== null
                ? array_filter(self::PRE_DUE_TRIGGERS, fn ($k) => $k === $daysOpt, ARRAY_FILTER_USE_KEY)
                : self::PRE_DUE_TRIGGERS;

            foreach ($triggers as $days => $triggerKey) {
                $targetDate = now()->addDays($days)->startOfDay();
                $this->processSchedules($targetDate, $triggerKey, false, $dryRun, $loanId);
            }
        }

        // ── Overdue reminders ─────────────────────────────────────────────────
        if (!$type || $type === 'overdue') {
            $triggers = $daysOpt !== null
                ? array_filter(self::OVERDUE_TRIGGERS, fn ($k) => $k === $daysOpt, ARRAY_FILTER_USE_KEY)
                : self::OVERDUE_TRIGGERS;

            foreach ($triggers as $days => $triggerKey) {
                $targetDate = now()->subDays($days)->startOfDay();
                $this->processSchedules($targetDate, $triggerKey, true, $dryRun, $loanId);
            }
        }

        $elapsed = round(microtime(true) - $start, 2);

        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Sent',         $this->stats['sent']],
                ['Failed',       $this->stats['failed']],
                ['Skipped',      $this->stats['skipped']],
                ['Dry-run only', $this->stats['dry_run']],
            ]
        );
        $this->info("Completed in {$elapsed}s");

        Log::info('[SendInstalmentReminders] Completed', array_merge(
            $this->stats, ['elapsed_s' => $elapsed]
        ));

        return self::SUCCESS;
    }

    private function processSchedules(
        Carbon $targetDate,
        string $triggerKey,
        bool   $isOverdue,
        bool   $dryRun,
        ?string $loanId
    ): void {
        $label = $isOverdue
            ? "Overdue {$targetDate->diffInDays(now())} days ({$triggerKey})"
            : "Due in {$targetDate->diffInDays(now())} days ({$triggerKey})";

        $this->line("  Processing: {$label}");

        // Find schedules with due_date on the target date that are not yet paid
        $query = LoanSchedule::query()
            ->whereDate('due_date', $targetDate)
            ->whereIn('status', $isOverdue ? ['overdue', 'partial'] : ['pending', 'partial'])
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue'])
                ->whereNotNull('disbursed_at'))
            ->with([
                'loan' => fn ($q) => $q->with([
                    'borrower:id,first_name,last_name,phone_primary',
                    'loanProduct:id,name,penalty_rate_percent,penalty_basis,grace_period_days',
                    'loanBalance:loan_id,total_outstanding,days_overdue,penalty_balance',
                    'penalties' => fn ($q) => $q->where('status', 'outstanding'),
                    'appliedBy:id,name,phone',
                ]),
            ]);

        if ($loanId) {
            $query->where('loan_id', $loanId);
        }

        $schedules = $query->get();

        foreach ($schedules as $schedule) {
            $loan    = $schedule->loan;
            $phone   = $loan->borrower?->phone_primary;

            if (!$phone) {
                $this->stats['skipped']++;
                continue;
            }

            // Guard: don't send to the same loan+trigger more than once per day
            $alreadySent = Reminder::where('loan_id', $loan->id)
                ->where('template_key', $triggerKey)
                ->whereDate('sent_at', now())
                ->exists();

            if ($alreadySent) {
                $this->stats['skipped']++;
                continue;
            }

            $context = $this->reminderService->buildContext($loan, $schedule);
            $body    = $this->reminderService->renderTemplate($triggerKey, $context);

            if (empty(trim($body))) {
                $this->warn("    Empty body for {$triggerKey} — is the template active?");
                $this->stats['skipped']++;
                continue;
            }

            if ($dryRun) {
                $this->line("    [DRY-RUN] {$phone} ← {$body}");
                $this->stats['dry_run']++;
                continue;
            }

            $result = $this->reminderService->sendRaw($phone, $body);

            // Persist a Reminder record regardless of outcome
            $reminder = Reminder::create([
                'loan_id'           => $loan->id,
                'loan_schedule_id'  => $schedule->id,
                'reminder_type'     => 'sms',
                'template_key'      => $triggerKey,
                'recipient_phone'   => $phone,
                'message_body'      => $body,
                'status'            => $result['status'],
                'provider_ref'      => $result['provider_ref'] ?? null,
                'sent_at'           => now(),
                'sent_by'           => null, // system-sent
            ]);

            if ($result['status'] === 'sent') {
                $this->stats['sent']++;
                // Schedule delivery status check in ~20 min
                \App\Jobs\CheckSmsDeliveryJob::dispatch($reminder)
                    ->delay(now()->addMinutes(20))
                    ->onQueue('default');
            } else {
                $this->stats['failed']++;
                Log::warning('[SendInstalmentReminders] SMS failed', [
                    'loan'    => $loan->loan_number,
                    'trigger' => $triggerKey,
                    'error'   => $result['error'] ?? 'unknown',
                ]);
            }
        }
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ReconcileLoanBalancesCommand
// File: app/Console/Commands/ReconcileLoanBalancesCommand.php
//
// Signature:  app:reconcile-loan-balances
// Schedule:   Daily at 02:00 ZST
//
// Rebuilds loan_balances from source records (payments, schedules, penalties)
// for every disbursed loan. Use after:
//   - Importing historical data
//   - Reversing a payment
//   - Manual DB corrections
//   - Debugging balance discrepancies
//
// Options:
//   --loan=ID     Rebuild a single loan only
//   --dry-run     Show discrepancies without updating
//   --mismatch    Only update loans where the balance differs from source truth
// ═══════════════════════════════════════════════════════════════════════════════
