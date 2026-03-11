<?php

namespace App\Mail;

class LoanApproved extends Mailable
{
    use Queueable, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        public readonly Loan $loan,
        public readonly User $approvedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to:      $this->loan->borrower->email,
            subject: 'Your Loan Application Has Been Approved — ' . $this->loan->loan_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-approved',
            with: [
                'loan'       => $this->loan->load([
                    'borrower',
                    'loanProduct:id,name,allow_early_settlement,grace_period_days',
                    'approvedBy:id,name,phone',
                ]),
                'approvedBy' => $this->approvedBy,
            ],
        );
    }

    /** Only send if borrower has an email address */
    public function shouldSend(): bool
    {
        return !empty($this->loan->borrower?->email);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanDisbursed — sent to borrower on disbursement
// Referenced in: SendLoanDisbursedNotification listener (email channel)
// ═══════════════════════════════════════════════════════════════════════════════
