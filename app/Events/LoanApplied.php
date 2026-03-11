<?php

namespace App\Events;

class LoanApplied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan $loan,
        public readonly User $appliedBy,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanApproved
// Fired by: LoanService::approve()
// Listeners: LogAuditEntry, SendLoanApprovedNotification
// ═══════════════════════════════════════════════════════════════════════════════
