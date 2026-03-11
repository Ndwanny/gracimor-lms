<?php

namespace App\Events;

class LoanClosed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan    $loan,
        public readonly ?User   $closedBy,
        public readonly string  $reason,
        /** 'early_settlement' | 'full_repayment' | 'write_off' | 'system_auto_close' */
        public readonly string  $closeType = 'full_repayment',
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// PaymentRecorded
// Fired by: PaymentService::record()
// Listeners: LogAuditEntry, UpdateLoanBalanceOnPayment, SendPaymentConfirmationSms,
//            MarkLoanCurrentIfFullyPaid
// ═══════════════════════════════════════════════════════════════════════════════
