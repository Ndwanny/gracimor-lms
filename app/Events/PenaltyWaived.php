<?php

namespace App\Events;

class PenaltyWaived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Penalty $penalty,
        public readonly Loan    $loan,
        public readonly User    $waivedBy,
        public readonly string  $reason,
        public readonly ?string $notes = null,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanOverdue
// Fired by: UpdateOverdueStatusesJob when a loan transitions to 'overdue'
// Listeners: LogAuditEntry, SendOverdueEscalationAlert (for P1 threshold)
// ═══════════════════════════════════════════════════════════════════════════════
