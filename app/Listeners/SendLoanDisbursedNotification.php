<?php

namespace App\Listeners;

use App\Events\LoanDisbursed;
use App\Services\ReminderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendLoanDisbursedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';
    public int    $tries = 3;

    public function __construct(protected ReminderService $reminderService) {}

    public function handle(LoanDisbursed $event): void
    {
        $loan     = $event->loan;
        $borrower = $loan->borrower;

        if (!$borrower?->phone_primary) {
            return;
        }

        $body = $this->reminderService->renderTemplate('loan_disbursed', [
            'first_name'    => $borrower->first_name,
            'loan_number'   => $loan->loan_number,
            'amount'        => number_format($loan->principal_amount, 2),
            'first_due'     => $loan->first_repayment_date?->format('d M Y'),
            'instalment'    => number_format($loan->monthly_instalment, 2),
            'officer_name'  => $event->disbursedBy->name,
            'officer_phone' => $event->disbursedBy->phone ?? config('gracimor.office_phone'),
            'company_name'  => config('gracimor.company_name', 'Gracimor Loans'),
        ]);

        $this->reminderService->sendRaw($borrower->phone_primary, $body, 'SMS');
    }

    public function failed(LoanDisbursed $event, \Throwable $exception): void
    {
        Log::error("[SendLoanDisbursedNotification] Failed for loan {$event->loan->loan_number}: " .
            $exception->getMessage());
    }
}
