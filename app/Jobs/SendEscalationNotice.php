<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEscalationNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'notifications';
    public int    $tries = 3;

    public function __construct(public readonly Loan $loan) {}

    public function handle(ReminderService $reminderService): void
    {
        $borrower = $this->loan->borrower;

        if (!$borrower?->phone_primary) {
            return;
        }

        $balance = $this->loan->loanBalance;

        $body = $reminderService->renderTemplate('escalation_notice', [
            'first_name'   => $borrower->first_name,
            'last_name'    => $borrower->last_name,
            'loan_number'  => $this->loan->loan_number,
            'total_due'    => number_format($balance?->total_outstanding ?? 0, 2),
            'days_overdue' => $balance?->days_overdue ?? 0,
            'company_name' => config('gracimor.company_name', 'Gracimor Loans'),
        ]);

        $result = $reminderService->sendRaw($borrower->phone_primary, $body, 'SMS');

        Log::info("[SendEscalationNotice] Sent to {$borrower->phone_primary} for loan {$this->loan->loan_number} — status: " .
            ($result['status'] ?? 'unknown'));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[SendEscalationNotice] Failed for loan {$this->loan->loan_number}: " . $exception->getMessage());
    }
}
