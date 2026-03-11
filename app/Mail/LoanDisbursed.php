<?php

namespace App\Mail;

class LoanDisbursed extends Mailable
{
    use Queueable, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        public readonly Loan $loan,
        public readonly User $disbursedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to:      $this->loan->borrower->email,
            subject: 'Your Loan Has Been Disbursed — ' . $this->loan->loan_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-disbursed',
            with: [
                'loan'        => $this->loan->load([
                    'borrower',
                    'loanProduct:id,name,allow_early_settlement,grace_period_days',
                    'loanSchedule',
                    'disbursedBy:id,name,phone',
                ]),
                'disbursedBy' => $this->disbursedBy,
            ],
        );
    }

    public function shouldSend(): bool
    {
        return !empty($this->loan->borrower?->email);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// PaymentConfirmation — sent to borrower after each payment
// Dispatch from: SendPaymentConfirmationSms listener (send email in parallel)
// ═══════════════════════════════════════════════════════════════════════════════
