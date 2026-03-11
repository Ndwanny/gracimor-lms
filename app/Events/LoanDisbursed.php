<?php

namespace App\Events;

class LoanDisbursed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan   $loan,
        public readonly User   $disbursedBy,
        public readonly string $disbursementMethod,
        public readonly ?string $reference = null,
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanRejected
// Fired by: LoanService::reject()
// Listeners: LogAuditEntry
// ═══════════════════════════════════════════════════════════════════════════════
