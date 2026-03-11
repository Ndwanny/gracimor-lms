<?php

namespace App\Services;

use App\Mail\LoanReminderMail;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\SmsTemplate;
use App\Services\Sms\AfricasTalkingDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderService
{
    /** Cache templates for 1 hour */
    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly AfricasTalkingDriver $smsDriver,
    ) {}

    // ── Email (Brevo SMTP) ────────────────────────────────────────────────────

    /**
     * Send an HTML email reminder via Brevo (or any configured SMTP mailer).
     * Queued — returns immediately with a synthetic result array.
     *
     * Returns null if the borrower has no email address, or if the template
     * context cannot be built.
     */
    public function sendEmail(
        Loan         $loan,
        LoanSchedule $schedule,
        string       $triggerKey,
    ): ?array {
        $email = $loan->borrower?->email;

        if (empty($email)) {
            Log::info("[ReminderService] No email for borrower on loan {$loan->loan_number} — skipping email.");
            return null;
        }

        $context = $this->buildContext($loan, $schedule);

        try {
            Mail::to($email)->queue(new LoanReminderMail($loan, $schedule, $triggerKey, $context));

            Log::info("[ReminderService] Email queued for {$triggerKey} — {$email} — loan {$loan->loan_number}");

            return [
                'channel'      => 'email',
                'status'       => 'sent',
                'provider_ref' => null,
                'phone'        => $email,
                'body'         => "(HTML email — {$triggerKey})",
                'cost'         => null,
                'error'        => null,
            ];
        } catch (\Throwable $e) {
            Log::error("[ReminderService] Email queue failed for {$email}", ['error' => $e->getMessage()]);
            return [
                'channel'      => 'email',
                'status'       => 'failed',
                'provider_ref' => null,
                'phone'        => $email,
                'body'         => "(HTML email — {$triggerKey})",
                'cost'         => null,
                'error'        => $e->getMessage(),
            ];
        }
    }

    // ── Africa's Talking SMS (reminder channel) ───────────────────────────────

    /**
     * Send a rendered SMS via Africa's Talking.
     * Used by the hybrid job for pre_due_3_days and overdue triggers.
     */
    public function sendSms(
        Loan         $loan,
        LoanSchedule $schedule,
        string       $triggerKey,
    ): ?array {
        $phone = $loan->borrower?->phone_primary;

        if (!$phone) {
            Log::warning("[ReminderService] No phone for SMS on loan {$loan->loan_number}");
            return null;
        }

        $template = $this->loadTemplate($triggerKey);

        if (!$template) {
            Log::warning("[ReminderService] No active template for SMS trigger: {$triggerKey}");
            return null;
        }

        $context           = $this->buildContext($loan, $schedule);
        $body              = $this->renderBody($template->body, $context);
        $result            = $this->smsDriver->send($phone, $body);
        $result['channel'] = 'sms';
        $result['body']    = $body;

        Log::info("[ReminderService] AT SMS {$triggerKey} — {$phone} — {$result['status']}", [
            'loan' => $loan->loan_number,
        ]);

        return $result;
    }

    // ── Africa's Talking WhatsApp ─────────────────────────────────────────────

    /**
     * Send a WhatsApp message via Africa's Talking Chat API.
     * Uses the same SMS template body — WhatsApp supports plain text.
     * Requires AT_WA_PRODUCT configured in the AT dashboard.
     */
    public function sendWhatsApp(
        Loan         $loan,
        LoanSchedule $schedule,
        string       $triggerKey,
    ): ?array {
        $phone = $loan->borrower?->phone_primary;

        if (!$phone) {
            Log::warning("[ReminderService] No phone for WhatsApp on loan {$loan->loan_number}");
            return null;
        }

        $template = $this->loadTemplate($triggerKey);

        if (!$template) {
            Log::warning("[ReminderService] No active template for WhatsApp trigger: {$triggerKey}");
            return null;
        }

        $context           = $this->buildContext($loan, $schedule);
        $body              = $this->renderBody($template->body, $context);
        $result            = $this->smsDriver->sendWhatsApp($phone, $body);
        $result['channel'] = 'whatsapp';
        $result['body']    = $body;

        Log::info("[ReminderService] AT WhatsApp {$triggerKey} — {$phone} — {$result['status']}", [
            'loan' => $loan->loan_number,
        ]);

        return $result;
    }

    // ── Africa's Talking SMS — direct send (used by event listeners) ─────────

    /**
     * Send SMS via Africa's Talking.
     * Called by listeners: SendPaymentConfirmationSms, SendLoanDisbursedNotification, SendEscalationNotice.
     */
    public function send(
        Loan          $loan,
        LoanSchedule  $schedule,
        string        $triggerKey,
        \Carbon\Carbon $sentAt,
    ): ?array {
        $template = $this->loadTemplate($triggerKey);

        if (!$template) {
            Log::warning("[ReminderService] No active template for trigger_key: {$triggerKey}");
            return null;
        }

        $phone = $loan->borrower?->phone_primary;
        if (!$phone) {
            Log::warning("[ReminderService] No phone number for borrower on loan {$loan->loan_number}");
            return null;
        }

        $context = $this->buildContext($loan, $schedule);
        $body    = $this->renderBody($template->body, $context);

        if (preg_match('/\{[a-z_]+\}/', $body)) {
            Log::warning("[ReminderService] Unresolved variables in template '{$triggerKey}'", [
                'body'        => $body,
                'loan_number' => $loan->loan_number,
            ]);
        }

        $result            = $this->smsDriver->send($phone, $body);
        $result['channel'] = 'sms';
        $result['body']    = $body;

        Log::info("[ReminderService] AT SMS {$triggerKey} — {$phone} — status: {$result['status']}", [
            'loan'        => $loan->loan_number,
            'provider_ref'=> $result['provider_ref'],
        ]);

        return $result;
    }

    // ── Raw send (used by listeners for payment confirmation, disbursement) ────

    public function sendRaw(string $phone, string $body, string $channel = 'sms'): array
    {
        $result            = $this->smsDriver->send($phone, $body);
        $result['channel'] = $channel;
        $result['body']    = $body;

        return $result;
    }

    // ── Template rendering (used by Mailables and controllers for preview) ─────

    public function renderTemplate(string $triggerKey, array $context): string
    {
        $template = $this->loadTemplate($triggerKey);

        if (!$template) {
            Log::error("[ReminderService] Template '{$triggerKey}' not found for renderTemplate().");
            return '';
        }

        return $this->renderBody($template->body, $context);
    }

    // ── Variable context builder ──────────────────────────────────────────────

    public function buildContext(Loan $loan, ?LoanSchedule $schedule = null): array
    {
        $borrower = $loan->borrower;
        $balance  = $loan->loanBalance;
        $product  = $loan->loanProduct;
        $officer  = $loan->appliedBy;

        $instalmentPrincipal = $schedule
            ? max(0, ($schedule->principal_portion ?? 0) - ($schedule->principal_paid ?? 0))
            : 0;
        $instalmentInterest = $schedule
            ? max(0, ($schedule->interest_portion ?? 0) - ($schedule->interest_paid ?? 0))
            : 0;
        $instalmentTotal = $instalmentPrincipal + $instalmentInterest;

        // Fall back to total_due on the schedule row if component columns are zero
        if ($instalmentTotal == 0 && $schedule) {
            $instalmentTotal = (float) ($schedule->total_due ?? 0);
        }

        $totalPenalties = $loan->penalties
            ? $loan->penalties->where('status', 'outstanding')->sum('penalty_amount')
            : 0;

        $latestPenalty = ($schedule && $loan->penalties)
            ? ($loan->penalties
                ->where('loan_schedule_id', $schedule->id)
                ->where('status', 'outstanding')
                ->last()?->penalty_amount ?? 0)
            : 0;

        return [
            'first_name'      => $borrower?->first_name ?? '',
            'last_name'       => $borrower?->last_name  ?? '',
            'loan_number'     => $loan->loan_number ?? '',
            'amount_due'      => number_format($instalmentTotal, 2),
            'due_date'        => $schedule
                ? (\Carbon\Carbon::parse($schedule->due_date)->format('d M Y'))
                : '',
            'instalment_no'   => $schedule
                ? ($schedule->instalment_number . ' of ' . $loan->term_months)
                : '',
            'days_overdue'    => $schedule && $schedule->due_date
                ? max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($schedule->due_date), false) * -1)
                : 0,
            'total_due'       => number_format($balance?->total_outstanding ?? 0, 2),
            'penalty_amount'  => number_format($latestPenalty, 2),
            'penalty_rate'    => $product ? number_format($product->penalty_rate_percent, 2) : '',
            'total_penalties' => number_format($totalPenalties, 2),
            'officer_name'    => $officer?->name ?? '',
            'officer_phone'   => $officer?->phone ?? config('gracimor.office_phone', ''),
            'company_name'    => config('gracimor.company_name', 'Gracimor Loans'),
            'company_phone'   => config('gracimor.office_phone', ''),
        ];
    }

    public function buildPaymentContext(\App\Models\Payment $payment, array $allocations = []): array
    {
        $loan     = $payment->loan;
        $borrower = $loan?->borrower;
        $balance  = $loan?->loanBalance;

        return [
            'first_name'   => $borrower?->first_name ?? '',
            'last_name'    => $borrower?->last_name  ?? '',
            'loan_number'  => $loan?->loan_number ?? '',
            'amount_paid'  => number_format($payment->amount_received, 2),
            'receipt'      => $payment->receipt_number ?? '',
            'balance_due'  => number_format($balance?->total_outstanding ?? 0, 2),
            'company_name' => config('gracimor.company_name', 'Gracimor Loans'),
            'company_phone'=> config('gracimor.office_phone', ''),
        ];
    }

    public function buildEscalationContext(Loan $loan): array
    {
        $borrower = $loan->borrower;
        $balance  = $loan->loanBalance;

        return [
            'first_name'   => $borrower?->first_name ?? '',
            'last_name'    => $borrower?->last_name  ?? '',
            'loan_number'  => $loan->loan_number ?? '',
            'total_due'    => number_format($balance?->total_outstanding ?? 0, 2),
            'days_overdue' => (string) 0, // computed from schedule
            'company_name' => config('gracimor.company_name', 'Gracimor Loans'),
            'company_phone'=> config('gracimor.office_phone', ''),
        ];
    }

    // ── Bulk send ─────────────────────────────────────────────────────────────

    public function sendBulkByTemplate(string $triggerKey, array $loans): array
    {
        $template = $this->loadTemplate($triggerKey);
        if (!$template) return [];

        $results = [];
        foreach ($loans as $loan) {
            $phone = $loan->borrower?->phone_primary;
            if (!$phone) continue;
            $context       = $this->buildContext($loan);
            $body          = $this->renderBody($template->body, $context);
            $results[$loan->id] = ['phone' => $phone, 'body' => $body];
        }

        if (empty($results)) return [];

        $bulkResults = $this->smsDriver->sendBulk(array_column($results, 'phone'), '');
        foreach ($results as $loanId => $r) {
            $results[$loanId]['result'] = $bulkResults[$r['phone']] ?? ['status' => 'unknown'];
        }

        return $results;
    }

    // ── Cache management ──────────────────────────────────────────────────────

    public function flushTemplateCache(string $triggerKey): void
    {
        Cache::forget("sms_template:{$triggerKey}");
    }

    public function flushAllTemplateCache(): void
    {
        $keys = SmsTemplate::pluck('trigger_key');
        foreach ($keys as $key) {
            Cache::forget("sms_template:{$key}");
        }
    }

    // ── Delivery status refresh ───────────────────────────────────────────────

    public function refreshDeliveryStatus(\App\Models\Reminder $reminder): void
    {
        if (!$reminder->provider_message_id || $reminder->status !== 'sent') {
            return;
        }

        $status = $this->smsDriver->checkStatus($reminder->provider_message_id);

        if (in_array($status, ['delivered', 'failed', 'rejected'])) {
            $reminder->update(['status' => $status]);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function loadTemplate(string $triggerKey): ?SmsTemplate
    {
        return Cache::remember(
            "sms_template:{$triggerKey}",
            self::CACHE_TTL,
            fn () => SmsTemplate::where('trigger_key', $triggerKey)
                ->where('is_active', true)
                ->first()
        );
    }

    private function renderBody(string $body, array $context): string
    {
        $search  = array_map(fn ($k) => '{' . $k . '}', array_keys($context));
        $replace = array_values($context);

        return str_replace($search, $replace, $body);
    }
}
