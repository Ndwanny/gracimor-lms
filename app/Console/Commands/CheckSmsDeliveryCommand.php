<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\ReminderService;
use App\Services\Sms\AfricasTalkingDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckSmsDeliveryCommand extends Command
{
    protected $signature = 'app:check-sms-delivery
        {--hours=2    : Check reminders sent within the last N hours}
        {--limit=500  : Maximum reminders to check per run}';

    protected $description = 'Batch-refresh SMS delivery statuses via Africa\'s Talking.';

    public function __construct(private readonly ReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?? 2);
        $limit = (int) ($this->option('limit') ?? 500);

        $this->info("Checking delivery status for reminders sent in the last {$hours} hour(s)...");

        $reminders = Reminder::where('status', 'sent')
            ->whereNotNull('provider_ref')
            ->where('sent_at', '>=', now()->subHours($hours))
            ->orderBy('sent_at')
            ->limit($limit)
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('No pending reminders to check.');
            return self::SUCCESS;
        }

        $this->line("  Found {$reminders->count()} reminders with status 'sent'.");

        $counts = ['delivered' => 0, 'failed' => 0, 'buffered' => 0, 'unchanged' => 0];

        $bar = $this->output->createProgressBar($reminders->count());
        $bar->start();

        foreach ($reminders as $reminder) {
            try {
                $before = $reminder->status;
                $this->reminderService->refreshDeliveryStatus($reminder);
                $reminder->refresh();
                $after = $reminder->status;

                if ($after !== $before) {
                    $counts[$after] = ($counts[$after] ?? 0) + 1;
                } else {
                    $counts['unchanged']++;
                }
            } catch (\Throwable $e) {
                Log::warning('[CheckSmsDelivery] Status check failed', [
                    'reminder_id' => $reminder->id,
                    'error'       => $e->getMessage(),
                ]);
                $counts['unchanged']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            array_map(
                fn ($s, $c) => [ucfirst($s), $c],
                array_keys($counts),
                array_values($counts)
            )
        );

        Log::info('[CheckSmsDelivery] Completed', $counts);

        return self::SUCCESS;
    }
}
