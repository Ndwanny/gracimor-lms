<?php

namespace App\Events;

class LoanOverdue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan $loan,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanEscalated
// Fired by: OverdueController::escalate()
// Listeners: LogAuditEntry, SendOverdueEscalationAlert
// ═══════════════════════════════════════════════════════════════════════════════
