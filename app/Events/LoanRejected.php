<?php

namespace App\Events;

class LoanRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan   $loan,
        public readonly User   $rejectedBy,
        public readonly string $reason,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanClosed
// Fired by: LoanService::close(), LoanService::earlySettle(),
//           UpdateOverdueStatusesJob (auto-close on zero balance)
// Listeners: LogAuditEntry, SendLoanClosedNotification
// ═══════════════════════════════════════════════════════════════════════════════
