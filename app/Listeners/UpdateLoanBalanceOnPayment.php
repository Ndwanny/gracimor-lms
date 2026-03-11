<?php

namespace App\Listeners;

use App\Events\PaymentRecorded;
use App\Models\LoanBalance;

class UpdateLoanBalanceOnPayment
{
    public function handle(PaymentRecorded $event): void
    {
        $loan        = $event->loan;
        $allocations = $event->allocations;
        $balance     = LoanBalance::firstOrNew(['loan_id' => $loan->id]);

        // Decrement each balance component based on the allocation
        $balance->principal_balance = max(
            0,
            ($balance->principal_balance ?? 0) - ($allocations['principal'] ?? 0)
        );
        $balance->interest_balance = max(
            0,
            ($balance->interest_balance ?? 0) - ($allocations['interest'] ?? 0)
        );
        $balance->penalty_balance = max(
            0,
            ($balance->penalty_balance ?? 0) - ($allocations['penalty'] ?? 0)
        );
        $balance->total_outstanding = $balance->principal_balance
            + $balance->interest_balance
            + $balance->penalty_balance;

        $balance->total_paid         = ($balance->total_paid ?? 0) + $event->payment->amount;
        $balance->last_payment_date  = $event->payment->payment_date;
        $balance->last_payment_amount= $event->payment->amount;
        $balance->save();

        // If the loan is now fully paid, transition to 'closed'
        if ($balance->total_outstanding <= 0.005 && in_array($loan->status, ['active', 'overdue'])) {
            $loan->update(['status' => 'closed']);
            $loan->statusHistory()->create([
                'previous_status' => $loan->getOriginal('status'),
                'new_status'      => 'closed',
                'notes'           => "Auto-closed: balance reached zero after payment {$event->payment->receipt_number}.",
                'changed_by'      => $event->recordedBy->id,
            ]);
        }
    }
}
