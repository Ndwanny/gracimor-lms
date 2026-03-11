<?php

namespace App\Mail;

use App\Models\Loan;
use App\Models\LoanSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        public readonly Loan          $loan,
        public readonly LoanSchedule  $schedule,
        public readonly string        $triggerKey,
        public readonly array         $context,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to:      $this->loan->borrower->email,
            subject: $this->resolveSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-reminder',
            with: [
                'context'    => $this->context,
                'triggerKey' => $this->triggerKey,
                'isOverdue'  => str_starts_with($this->triggerKey, 'overdue'),
                'isDueToday' => $this->triggerKey === 'due_today',
                'company'    => [
                    'name'    => config('gracimor.company_name',    'Gracimor Microfinance Ltd'),
                    'phone'   => config('gracimor.office_phone',    '+260211000001'),
                    'email'   => config('gracimor.company_email',   'info@gracimor.co.zm'),
                    'address' => config('gracimor.company_address', 'Lusaka, Zambia'),
                    'boz'     => config('gracimor.boz_licence',     ''),
                ],
            ],
        );
    }

    public function shouldSend(): bool
    {
        return !empty($this->loan->borrower?->email);
    }

    private function resolveSubject(): string
    {
        $loanNum = $this->loan->loan_number;

        return match (true) {
            str_starts_with($this->triggerKey, 'overdue') => "⚠️ Overdue Payment — Loan {$loanNum}",
            $this->triggerKey === 'due_today'              => "Payment Due Today — Loan {$loanNum}",
            $this->triggerKey === 'pre_due_1_day'          => "Payment Due Tomorrow — Loan {$loanNum}",
            $this->triggerKey === 'pre_due_3_days'         => "Payment Due in 3 Days — Loan {$loanNum}",
            default                                        => "Upcoming Payment Reminder — Loan {$loanNum}",
        };
    }
}
