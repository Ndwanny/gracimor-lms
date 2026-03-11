<?php

namespace App\Mail;

class DailyPortfolioReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array  $data,
        public readonly string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Gracimor Loans — Daily Portfolio Report: ' . $this->data['date'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-portfolio-report',
            with: [
                'data'          => $this->data,
                'recipientName' => $this->recipientName,
            ],
        );
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanEscalationAlert — Mailable
// ═══════════════════════════════════════════════════════════════════════════════
