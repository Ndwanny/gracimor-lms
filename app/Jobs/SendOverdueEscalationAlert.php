<?php

namespace App\Jobs;

use App\Events\LoanApplied;
use App\Events\LoanApproved;
use App\Events\LoanClosed;
use App\Events\LoanDisbursed;
use App\Events\LoanEscalated;
use App\Events\LoanOverdue;
use App\Events\LoanRejected;
use App\Events\PaymentRecorded;
use App\Events\PenaltyApplied;
use App\Events\PenaltyWaived;
use App\Listeners\LogAuditEntry;
use App\Listeners\MarkLoanOverdueOnThreshold;
use App\Listeners\SendLoanDisbursedNotification;
use App\Listeners\SendOverdueEscalationAlert;
use App\Listeners\SendPaymentConfirmationSms;
use App\Listeners\UpdateLoanBalanceOnPayment;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class SendOverdueEscalationAlert implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(LoanEscalated $event): void
    {
        $loan = $event->loan;

        // Notify all managers and CEO
        $recipients = User::whereIn('role', ['ceo', 'manager'])
            ->where('is_active', true)
            ->get();

        foreach ($recipients as $recipient) {
            // Internal system notification
            \App\Models\Notification::create([
                'user_id' => $recipient->id,
                'type'    => 'loan_escalated',
                'title'   => "Loan escalated to {$event->escalationType}",
                'message' => "Loan {$loan->loan_number} ({$loan->borrower?->full_name}) was escalated " .
                             "by {$event->escalatedBy->name} — assigned to: {$event->assignedTo}." .
                             ($event->notes ? " Note: {$event->notes}" : ''),
                'data'    => [
                    'loan_id'          => $loan->id,
                    'loan_number'      => $loan->loan_number,
                    'escalation_type'  => $event->escalationType,
                    'assigned_to'      => $event->assignedTo,
                    'escalated_by'     => $event->escalatedBy->name,
                ],
            ]);

            // Email alert
            if ($recipient->email) {
                Mail::to($recipient->email)->send(
                    new \App\Mail\LoanEscalationAlert($loan, $event, $recipient)
                );
            }
        }
    }
}
