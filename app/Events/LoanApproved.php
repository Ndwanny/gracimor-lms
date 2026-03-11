<?php

namespace App\Events;

class LoanApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan   $loan,
        public readonly User   $approvedBy,
        public readonly ?string $notes = null,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanDisbursed
// Fired by: LoanService::disburse()
// Listeners: LogAuditEntry, SendLoanDisbursedNotification, UpdateLoanBalanceOnDisburse
// ═══════════════════════════════════════════════════════════════════════════════
