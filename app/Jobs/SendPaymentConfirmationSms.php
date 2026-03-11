<?php

namespace App\Jobs;

class SendPaymentConfirmationSms implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';
    public int    $tries = 3;
    public int    $backoff = 30; // seconds between retries

    public function __construct(protected ReminderService $reminderService) {}

    public function handle(PaymentRecorded $event): void
    {
        $loan     = $event->loan;
        $borrower = $loan->borrower;

        if (!$borrower?->phone_primary) {
            return;
        }

        $balance = $loan->loanBalance;

        $body = $this->reminderService->renderTemplate('payment_confirmation', [
            'first_name'   => $borrower->first_name,
            'loan_number'  => $loan->loan_number,
            'amount_paid'  => number_format($event->payment->amount, 2),
            'receipt'      => $event->payment->receipt_number,
            'balance_due'  => number_format($balance?->total_outstanding ?? 0, 2),
            'company_name' => config('gracimor.company_name', 'Gracimor Loans'),
        ]);

        $result = $this->reminderService->sendRaw($borrower->phone_primary, $body, 'SMS');

        Log::info("[SendPaymentConfirmationSms] Sent to {$borrower->phone_primary} — " .
            "receipt: {$event->payment->receipt_number} — status: " . ($result['status'] ?? 'unknown'));
    }

    public function failed(PaymentRecorded $event, \Throwable $exception): void
    {
        Log::error("[SendPaymentConfirmationSms] Failed for payment {$event->payment->receipt_number}: " .
            $exception->getMessage());
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// Listener 4: SendLoanDisbursedNotification
//
// Sends an SMS/WhatsApp notification to the borrower when their loan is disbursed.
// Queued.
// ═══════════════════════════════════════════════════════════════════════════════
