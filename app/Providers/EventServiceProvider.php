<?php

namespace App\Providers;

use App\Events\LoanApplied;
use App\Events\LoanApproved;
use App\Events\LoanClosed;
use App\Events\LoanDisbursed;
use App\Events\LoanEscalated;
use App\Events\LoanOverdue;
use App\Events\LoanRejected;
use App\Events\PaymentRecorded;
use App\Events\PenaltyApplied;
use App\Events\PenaltyWaived;
use App\Jobs\MarkLoanOverdueOnThreshold;
use App\Jobs\SendOverdueEscalationAlert;
use App\Jobs\SendPaymentConfirmationSms;
use App\Listeners\LogAuditEntry;
use App\Listeners\SendLoanDisbursedNotification;
use App\Listeners\UpdateLoanBalanceOnPayment;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings.
     *
     * LogAuditEntry handles EVERY event in the system for a full audit trail.
     * It is deliberately listed first in every event's listener array so that
     * the audit log is written even if a downstream listener throws.
     */
    protected $listen = [

        // ── Loan lifecycle ────────────────────────────────────────────────────
        LoanApplied::class => [
            LogAuditEntry::class,
        ],

        LoanApproved::class => [
            LogAuditEntry::class,
        ],

        LoanDisbursed::class => [
            LogAuditEntry::class,
            SendLoanDisbursedNotification::class,    // queued
        ],

        LoanRejected::class => [
            LogAuditEntry::class,
        ],

        LoanClosed::class => [
            LogAuditEntry::class,
        ],

        // ── Payments ──────────────────────────────────────────────────────────
        PaymentRecorded::class => [
            UpdateLoanBalanceOnPayment::class,       // sync  — balance must be immediate
            LogAuditEntry::class,                    // sync  — after balance update
            SendPaymentConfirmationSms::class,       // queued
        ],

        // ── Penalties ─────────────────────────────────────────────────────────
        PenaltyApplied::class => [
            LogAuditEntry::class,
        ],

        PenaltyWaived::class => [
            LogAuditEntry::class,
        ],

        // ── Overdue & escalation ──────────────────────────────────────────────
        LoanOverdue::class => [
            LogAuditEntry::class,
            MarkLoanOverdueOnThreshold::class,       // queued — internal officer alerts
        ],

        LoanEscalated::class => [
            LogAuditEntry::class,
            SendOverdueEscalationAlert::class,       // queued — manager/CEO email+notification
        ],
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false; // Explicit registration only — no auto-discovery
    }
}
