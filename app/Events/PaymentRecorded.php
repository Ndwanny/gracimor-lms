<?php

namespace App\Events;

class PaymentRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
        public readonly Loan    $loan,
        public readonly User    $recordedBy,
        /** Keyed array: ['principal' => x, 'interest' => y, 'penalty' => z] */
        public readonly array   $allocations = [],
    ) {}
}


// ═══════════════════════════════════════════════════════════════════════════════
// PenaltyApplied
// Fired by: ApplyDailyPenaltiesJob, PenaltyService::applyDailyPenalties()
// Listeners: LogAuditEntry
// (No SMS — borrower SMS for overdue is handled by SendInstalmentRemindersJob)
// ═══════════════════════════════════════════════════════════════════════════════
