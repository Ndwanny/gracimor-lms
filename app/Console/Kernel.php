<?php

namespace App\Console;

class Kernel extends ConsoleKernel
{
    /**
     * All custom Artisan commands.
     * Laravel 11 auto-discovers commands in app/Console/Commands/,
     * but listing here makes IDE completion reliable.
     */
    protected $commands = [
        UpdateOverdueStatusesCommand::class,
        ApplyDailyPenaltiesCommand::class,
        SendInstalmentRemindersCommand::class,
        ReconcileLoanBalancesCommand::class,
        GeneratePortfolioReportCommand::class,
        PruneAuditLogsCommand::class,
        ExportLoansCommand::class,
        CheckSmsDeliveryCommand::class,
        CreateUserCommand::class,
        SmsPreviewCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // ── 06:00 ZST — Mark overdue statuses (must run before penalties) ─────
        $schedule
            ->command('app:update-overdue-statuses')
            ->dailyAt('06:00')
            ->timezone('Africa/Lusaka')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'))
            ->onFailure(fn () => \Illuminate\Support\Facades\Log::error(
                'SCHEDULER: app:update-overdue-statuses failed'
            ));

        // ── 06:15 ZST — Apply daily penalties ────────────────────────────────
        $schedule
            ->command('app:apply-daily-penalties')
            ->dailyAt('06:15')
            ->timezone('Africa/Lusaka')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'))
            ->onFailure(fn () => \Illuminate\Support\Facades\Log::error(
                'SCHEDULER: app:apply-daily-penalties failed'
            ));

        // ── 07:00 ZST — Daily portfolio report email ──────────────────────────
        $schedule
            ->command('app:generate-portfolio-report')
            ->dailyAt('07:00')
            ->timezone('Africa/Lusaka')
            ->withoutOverlapping(5)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ── 07:30 ZST Mon–Fri — Hybrid reminders (email always; SMS+WhatsApp on 3-day & overdue) ──
        $schedule
            ->command('app:send-instalment-reminders')
            ->weekdays()
            ->at('07:30')
            ->timezone('Africa/Lusaka')
            ->withoutOverlapping(30)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'))
            ->onFailure(fn () => \Illuminate\Support\Facades\Log::error(
                'SCHEDULER: app:send-instalment-reminders failed'
            ));

        // ── 02:00 ZST — Reconcile all loan balances ───────────────────────────
        $schedule
            ->command('app:reconcile-loan-balances')
            ->dailyAt('02:00')
            ->timezone('Africa/Lusaka')
            ->withoutOverlapping(60)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ── 00:30 ZST — Prune old audit logs ─────────────────────────────────
        $schedule
            ->command('app:prune-audit-logs --days=730')
            ->dailyAt('00:30')
            ->timezone('Africa/Lusaka')
            ->withoutOverlapping(30)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // ── 00:45 ZST — Prune stale failed jobs older than 7 days ────────────
        $schedule
            ->command('queue:prune-failed --hours=168')
            ->dailyAt('00:45')
            ->timezone('Africa/Lusaka');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
