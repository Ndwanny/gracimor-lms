<?php

namespace App\Mail;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        public readonly Payment $payment,
        public readonly array   $allocations,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to:      $this->payment->loan->borrower->email,
            subject: 'Payment Receipt ' . $this->payment->receipt_number .
                     ' — ' . config('gracimor.company_name', 'Gracimor Loans'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'payment'     => $this->payment->load([
                    'loan.borrower',
                    'loan.loanProduct:id,name',
                    'loan.loanBalance',
                    'paymentAllocations',
                    'recordedBy:id,name',
                ]),
                'allocations' => $this->allocations,
            ],
        );
    }

    public function shouldSend(): bool
    {
        return !empty($this->payment->loan->borrower?->email);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanClosed — sent to borrower when loan is fully repaid or early-settled
// Dispatch from: LoanClosedListener or auto-close in UpdateOverdueStatusesJob
// ═══════════════════════════════════════════════════════════════════════════════
