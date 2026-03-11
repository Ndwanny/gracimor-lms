<?php

namespace App\Events;

class PenaltyApplied
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Penalty $penalty,
        public readonly Loan    $loan,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// PenaltyWaived
// Fired by: PenaltyService::waive(), PenaltyService::bulkWaive()
// Listeners: LogAuditEntry
// ═══════════════════════════════════════════════════════════════════════════════
