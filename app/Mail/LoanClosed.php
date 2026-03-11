<?php

namespace App\Mail;

class LoanClosed extends Mailable
{
    use Queueable, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        public readonly Loan   $loan,
        public readonly string $closeType,  // 'full_repayment' | 'early_settlement'
        public readonly ?User  $closedBy = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->closeType) {
            'early_settlement' => 'Loan Settled Early — ' . $this->loan->loan_number,
            default            => 'Loan Fully Repaid — ' . $this->loan->loan_number,
        };

        return new Envelope(
            to:      $this->loan->borrower->email,
            subject: $subject . ' · ' . config('gracimor.company_name', 'Gracimor Loans'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-closed',
            with: [
                'loan'      => $this->loan->load([
                    'borrower',
                    'loanProduct:id,name',
                    'loanBalance',
                    'payments' => fn ($q) => $q->where('status', '!=', 'reversed')
                                              ->orderByDesc('payment_date'),
                ]),
