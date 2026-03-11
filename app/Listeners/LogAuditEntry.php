<?php

namespace App\Listeners;

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
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class LogAuditEntry
{
    public function handle(object $event): void
    {
        try {
            [$action, $description, $userId, $entityType, $entityId, $metadata] = match (true) {

                $event instanceof LoanApplied => [
                    'loan.applied',
                    "Loan application submitted: {$event->loan->loan_number} — K " .
                        number_format($event->loan->principal_amount, 2) .
                        " for {$event->loan->borrower?->full_name}",
                    $event->appliedBy->id,
                    'Loan',
                    $event->loan->id,
                    ['loan_number' => $event->loan->loan_number, 'principal' => $event->loan->principal_amount],
                ],

                $event instanceof LoanApproved => [
                    'loan.approved',
                    "Loan approved: {$event->loan->loan_number}" .
                        ($event->notes ? " — {$event->notes}" : ''),
                    $event->approvedBy->id,
                    'Loan',
                    $event->loan->id,
                    ['loan_number' => $event->loan->loan_number, 'notes' => $event->notes],
                ],

                $event instanceof LoanDisbursed => [
                    'loan.disbursed',
                    "Loan disbursed: {$event->loan->loan_number} — K " .
                        number_format($event->loan->principal_amount, 2) .
                        " via {$event->disbursementMethod}" .
                        ($event->reference ? " (ref: {$event->reference})" : ''),
                    $event->disbursedBy->id,
                    'Loan',
                    $event->loan->id,
                    ['method' => $event->disbursementMethod, 'reference' => $event->reference],
                ],

                $event instanceof LoanRejected => [
                    'loan.rejected',
                    "Loan rejected: {$event->loan->loan_number} — {$event->reason}",
                    $event->rejectedBy->id,
                    'Loan',
                    $event->loan->id,
                    ['reason' => $event->reason],
                ],

                $event instanceof LoanClosed => [
                    'loan.closed',
                    "Loan closed: {$event->loan->loan_number} — {$event->closeType}" .
                        ($event->reason ? " ({$event->reason})" : ''),
                    $event->closedBy?->id,
                    'Loan',
                    $event->loan->id,
                    ['close_type' => $event->closeType, 'reason' => $event->reason],
                ],

                $event instanceof PaymentRecorded => [
                    'payment.recorded',
                    "Payment recorded: {$event->payment->receipt_number} — K " .
                        number_format($event->payment->amount, 2) .
                        " for {$event->loan->loan_number}" .
                        " ({$event->loan->borrower?->full_name})",
                    $event->recordedBy->id,
                    'Payment',
                    $event->payment->id,
                    [
                        'receipt'     => $event->payment->receipt_number,
                        'amount'      => $event->payment->amount,
                        'method'      => $event->payment->payment_method,
                        'allocations' => $event->allocations,
                    ],
                ],

                $event instanceof PenaltyApplied => [
                    'penalty.applied',
                    "Penalty applied: K " . number_format($event->penalty->amount, 2) .
                        " on {$event->loan->loan_number}" .
                        " ({$event->penalty->days_overdue} days overdue)",
                    null, // system
                    'Penalty',
                    $event->penalty->id,
                    [
                        'amount'      => $event->penalty->amount,
                        'days_overdue'=> $event->penalty->days_overdue,
                        'basis'       => $event->penalty->basis,
                    ],
                ],

                $event instanceof PenaltyWaived => [
                    'penalty.waived',
                    "Penalty waived: K " . number_format($event->penalty->amount, 2) .
                        " on {$event->loan->loan_number} — reason: {$event->reason}" .
                        ($event->notes ? " | {$event->notes}" : ''),
                    $event->waivedBy->id,
                    'Penalty',
                    $event->penalty->id,
                    ['amount' => $event->penalty->amount, 'reason' => $event->reason],
                ],

                $event instanceof LoanOverdue => [
                    'loan.became_overdue',
                    "Loan transitioned to overdue: {$event->loan->loan_number}" .
                        " — {$event->loan->loanBalance?->days_overdue} days",
                    null, // system job
                    'Loan',
                    $event->loan->id,
                    ['days_overdue' => $event->loan->loanBalance?->days_overdue],
                ],

                $event instanceof LoanEscalated => [
                    'loan.escalated',
                    "Loan escalated: {$event->loan->loan_number} — {$event->escalationType}" .
                        " assigned to {$event->assignedTo}",
                    $event->escalatedBy->id,
                    'Loan',
                    $event->loan->id,
                    [
                        'type'        => $event->escalationType,
                        'assigned_to' => $event->assignedTo,
                        'notes'       => $event->notes,
                    ],
                ],

                default => [null, null, null, null, null, []],
            };

            if (!$action) {
                return; // Unknown event — skip
            }

            AuditLog::create([
                'user_id'        => $userId,
                'action'         => $action,
                'auditable_type' => $entityType,
                'auditable_id'   => $entityId,
                'description'    => $description,
                'metadata'       => $metadata,
                'ip_address'     => request()?->ip(),
                'user_agent'     => request()?->userAgent(),
            ]);

        } catch (\Throwable $e) {
            // Audit log failure must never break the main flow
            Log::error("[LogAuditEntry] Failed to write audit log for " . get_class($event) . ": " . $e->getMessage());
        }
    }
}
