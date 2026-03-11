<?php

namespace App\Events;

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
use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\User;
use App\Services\ReminderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LoanEscalated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Loan   $loan,
        public readonly User   $escalatedBy,
        public readonly string $escalationType,
        public readonly string $assignedTo,
        public readonly ?string $notes = null,
    ) {}
}
