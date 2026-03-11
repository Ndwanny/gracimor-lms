<?php

namespace App\Mail;

class LoanEscalationAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly \App\Models\Loan  $loan,
        public readonly \App\Events\LoanEscalated $event,
        public readonly \App\Models\User  $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Loan Escalated: {$this->loan->loan_number} — {$this->event->escalationType}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-escalation-alert',
            with: [
                'loan'      => $this->loan,
                'event'     => $this->event,
                'recipient' => $this->recipient,
            ],
        );
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// Gracimor Config — config/gracimor.php
// ═══════════════════════════════════════════════════════════════════════════════

/*
 * Place in config/gracimor.php
 *
 * return [
 *     'company_name'        => env('GRACIMOR_COMPANY_NAME', 'Gracimor Microfinance Ltd'),
 *     'office_phone'        => env('GRACIMOR_OFFICE_PHONE', '+260 977 000 001'),
 *     'currency_symbol'     => env('GRACIMOR_CURRENCY', 'K'),
 *     'timezone'            => env('GRACIMOR_TIMEZONE', 'Africa/Lusaka'),
 *     'penalty_job_enabled' => env('GRACIMOR_PENALTY_JOB', true),
 *     'sms_provider'        => env('SMS_PROVIDER', 'africastalking'),
 *     'sms_sender_id'       => env('SMS_SENDER_ID', 'GRACIMOR'),
 * ];
 */


// ═══════════════════════════════════════════════════════════════════════════════
// Queue Configuration Note — .env additions
// ═══════════════════════════════════════════════════════════════════════════════

/*
 * Add to .env:
 *
 * QUEUE_CONNECTION=redis          # or database for simpler setups
 *
 * # Named queues — workers can be started with different priorities:
 * # php artisan queue:work redis --queue=high,notifications,default --tries=3 --sleep=3
 * #
 * # Or run separate workers per queue:
 * # php artisan queue:work redis --queue=high          --tries=3  (for critical jobs)
 * # php artisan queue:work redis --queue=notifications --tries=3  (for SMS sending)
 * # php artisan queue:work redis --queue=default       --tries=2  (for reports, balances)
 *
 * # Supervisor config example (/etc/supervisor/conf.d/gracimor-worker.conf):
 * #
 * # [program:gracimor-high]
 * # command=php /var/www/gracimor/artisan queue:work redis --queue=high --tries=3 --sleep=3
 * # autostart=true
 * # autorestart=true
 * # numprocs=2
 * # stdout_logfile=/var/log/gracimor/worker-high.log
 * #
 * # [program:gracimor-notifications]
 * # command=php /var/www/gracimor/artisan queue:work redis --queue=notifications --tries=3 --sleep=5
 * # autostart=true
 * # autorestart=true
 * # numprocs=1
 * # stdout_logfile=/var/log/gracimor/worker-notifications.log
 */
