<?php

namespace App\Jobs;

use App\Models\LoanSchedule;
use App\Models\Payment;
use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendInstalmentRemindersJob
 *
 * Hybrid reminder dispatch strategy (runs daily):
 *
 *   ALL triggers      → Email (Brevo SMTP, free)
 *   pre_due_3_days    → Email + Twilio SMS (if no payment in last 7 days)
 *   Any overdue       → Email + Twilio SMS + Twilio WhatsApp
 *
 * This keeps costs low while escalating urgency via paid channels only when necessary.
 */
class SendInstalmentRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600;

    // Days-before triggers (positive = days before due_date)
    private const PRE_DUE_TRIGGERS = [
        7 => 'pre_due_7_days',
        3 => 'pre_due_3_days',
        1 => 'pre_due_1_day',
        0 => 'due_today',
    ];

    // Days-after triggers (positive = days past due_date)
    private const OVERDUE_TRIGGERS = [
        1  => 'overdue_1_day',
        7  => 'overdue_7_days',
        14 => 'overdue_14_days',
        30 => 'overdue_30_days',
    ];

    // Triggers that escalate to paid channels
    private const SMS_TRIGGERS      = ['pre_due_3_days'];
    private const WHATSAPP_TRIGGERS = ['overdue_1_day', 'overdue_7_days', 'overdue_14_days', 'overdue_30_days'];

    public function __construct(
        public readonly ?string $asOfDate = null,
    ) {}

    public function handle(ReminderService $reminderService): void
    {
        $asOf  = $this->asOfDate ?? today()->toDateString();
        $today = now()->parse($asOf);
        $start = microtime(true);

        Log::info("[SendInstalmentRemindersJob] Starting — as_of: {$asOf}");

        $emailSent    = 0;
        $smsSent      = 0;
        $whatsappSent = 0;
        $skipped      = 0;

        // ── Pre-due reminders ─────────────────────────────────────────────────
        foreach (self::PRE_DUE_TRIGGERS as $daysOffset => $triggerKey) {
            $targetDate = $today->copy()->addDays($daysOffset)->toDateString();

            $schedules = LoanSchedule::query()
                ->whereDate('due_date', $targetDate)
                ->whereIn('status', ['pending', 'partial'])
                ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']))
                ->with([
                    'loan.borrower:id,first_name,last_name,phone_primary,email',
                    'loan.loanBalance',
                    'loan.loanProduct:id,name,penalty_rate_percent',
                    'loan.appliedBy:id,name,phone',
                    'loan.penalties' => fn ($q) => $q->where('status', 'outstanding'),
                ])
                ->get();

            Log::info("[SendInstalmentRemindersJob] {$triggerKey}: {$schedules->count()} schedules");

            foreach ($schedules as $schedule) {
                $loan = $schedule->loan;

                // ── Email (always) ─────────────────────────────────────────
                if (!$this->alreadySentOnChannel($loan->id, $schedule->id, $triggerKey, $asOf, 'email')) {
                    $result = $reminderService->sendEmail($loan, $schedule, $triggerKey);
                    if ($result) {
                        $emailSent++;
                        $this->recordReminder($loan->id, $schedule->id, $triggerKey, $asOf, $result);
                    }
                } else {
                    $skipped++;
                }

                // ── Twilio SMS (3-days-before only, if no recent payment) ──
                if (in_array($triggerKey, self::SMS_TRIGGERS)) {
                    if (!$this->recentPaymentMade($loan->id, 7)
                        && !$this->alreadySentOnChannel($loan->id, $schedule->id, $triggerKey, $asOf, 'sms')
                    ) {
                        $result = $reminderService->sendSms($loan, $schedule, $triggerKey);
                        if ($result) {
                            $smsSent++;
                            $this->recordReminder($loan->id, $schedule->id, $triggerKey, $asOf, $result);
                        }
                    }
                }
            }
        }

        // ── Overdue reminders ─────────────────────────────────────────────────
        foreach (self::OVERDUE_TRIGGERS as $daysOverdue => $triggerKey) {
            $targetDate = $today->copy()->subDays($daysOverdue)->toDateString();

            $schedules = LoanSchedule::query()
                ->whereDate('due_date', $targetDate)
                ->whereIn('status', ['overdue', 'partial'])
                ->whereHas('loan', fn ($q) => $q->where('status', 'overdue'))
                ->with([
                    'loan.borrower:id,first_name,last_name,phone_primary,email',
                    'loan.loanBalance',
                    'loan.loanProduct:id,name,penalty_rate_percent',
                    'loan.appliedBy:id,name,phone',
                    'loan.penalties' => fn ($q) => $q->where('status', 'outstanding'),
                ])
                ->get();

            Log::info("[SendInstalmentRemindersJob] {$triggerKey}: {$schedules->count()} schedules");

            foreach ($schedules as $schedule) {
                $loan = $schedule->loan;

                // ── Email (always) ─────────────────────────────────────────
                if (!$this->alreadySentOnChannel($loan->id, $schedule->id, $triggerKey, $asOf, 'email')) {
                    $result = $reminderService->sendEmail($loan, $schedule, $triggerKey);
                    if ($result) {
                        $emailSent++;
                        $this->recordReminder($loan->id, $schedule->id, $triggerKey, $asOf, $result);
                    }
                } else {
                    $skipped++;
                }

                // ── Twilio SMS (all overdue triggers) ──────────────────────
                if (!$this->alreadySentOnChannel($loan->id, $schedule->id, $triggerKey, $asOf, 'sms')) {
                    $result = $reminderService->sendSms($loan, $schedule, $triggerKey);
                    if ($result) {
                        $smsSent++;
                        $this->recordReminder($loan->id, $schedule->id, $triggerKey, $asOf, $result);
                    }
                }

                // ── Twilio WhatsApp (all overdue triggers) ─────────────────
                if (!$this->alreadySentOnChannel($loan->id, $schedule->id, $triggerKey, $asOf, 'whatsapp')) {
                    $result = $reminderService->sendWhatsApp($loan, $schedule, $triggerKey);
                    if ($result) {
                        $whatsappSent++;
                        $this->recordReminder($loan->id, $schedule->id, $triggerKey, $asOf, $result);
                    }
                }
            }
        }

        $elapsed = round(microtime(true) - $start, 3);
        Log::info(
            "[SendInstalmentRemindersJob] Completed in {$elapsed}s — "
            . "email: {$emailSent}, sms: {$smsSent}, whatsapp: {$whatsappSent}, skipped: {$skipped}"
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Check whether a reminder for this loan+schedule+trigger+channel was already
     * sent today. Prevents duplicate sends on re-runs.
     */
    private function alreadySentOnChannel(
        int    $loanId,
        int    $scheduleId,
        string $triggerKey,
        string $date,
        string $channel,
    ): bool {
        return Reminder::where('loan_id', $loanId)
            ->where('loan_schedule_id', $scheduleId)
            ->where('trigger_type', $triggerKey)
            ->where('channel', $channel)
            ->whereDate('sent_at', $date)
            ->whereIn('status', ['sent', 'delivered'])
            ->exists();
    }

    /**
     * Check whether a payment has been received on this loan in the last N days.
     * Used to decide whether to escalate to paid SMS on pre_due_3_days.
     */
    private function recentPaymentMade(int $loanId, int $withinDays): bool
    {
        return Payment::where('loan_id', $loanId)
            ->where('payment_date', '>=', now()->subDays($withinDays)->toDateString())
            ->exists();
    }

    /**
     * Persist a Reminder record so this message is not resent on re-run.
     */
    private function recordReminder(
        int    $loanId,
        int    $scheduleId,
        string $triggerKey,
        string $date,
        array  $result,
    ): void {
        // Look up borrower_id from the loan to satisfy the FK constraint
        $borrowerId = \App\Models\Loan::where('id', $loanId)->value('borrower_id');

        Reminder::create([
            'loan_id'            => $loanId,
            'loan_schedule_id'   => $scheduleId,
            'borrower_id'        => $borrowerId,
            'channel'            => $result['channel'],
            'trigger_type'       => $triggerKey,
            'message_body'       => $result['body'] ?? '',
            'recipient_number'   => $result['phone'] ?? null,
            'provider_message_id'=> $result['provider_ref'] ?? null,
            'status'             => $result['status'] ?? 'sent',
            'sent_at'            => now()->parse($date),
            'is_automated'       => true,
            'triggered_by'       => null,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[SendInstalmentRemindersJob] FAILED: " . $exception->getMessage(), [
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
