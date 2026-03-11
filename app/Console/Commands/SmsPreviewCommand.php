<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\SmsTemplate;
use App\Services\ReminderService;
use App\Services\Sms\AfricasTalkingDriver;
use Illuminate\Console\Command;

class SmsPreviewCommand extends Command
{
    protected $signature = 'sms:preview
        {template?  : trigger_key of the template to preview}
        {loan?      : Loan ID or loan_number to render against}
        {--list     : List all templates in a table}
        {--send     : Actually dispatch the SMS (use with caution)}
        {--context= : JSON string of variable overrides e.g. {"first_name":"Mwansa"}}';

    protected $description = 'Preview or test an SMS template against live or demo data.';

    public function __construct(
        private readonly ReminderService      $service,
        private readonly AfricasTalkingDriver $smsDriver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // ── --list mode ───────────────────────────────────────────────────────
        if ($this->option('list')) {
            $this->renderTemplateList();
            return self::SUCCESS;
        }

        // ── Resolve template ──────────────────────────────────────────────────
        $triggerKey = $this->argument('template')
            ?? $this->ask('Enter template trigger_key (or leave blank to see list)');

        if (!$triggerKey) {
            $this->renderTemplateList();
            return self::SUCCESS;
        }

        $template = SmsTemplate::where('trigger_key', $triggerKey)->first();

        if (!$template) {
            $this->error("Template '{$triggerKey}' not found.");
            $this->line('Run with --list to see all available templates.');
            return self::FAILURE;
        }

        // ── Display template metadata ─────────────────────────────────────────
        $this->newLine();
        $this->line("<fg=yellow>Template:</> {$template->name}");
        $this->line("<fg=yellow>Key:</>      {$template->trigger_key}");
        $this->line("<fg=yellow>Category:</> {$template->category}");
        $this->line("<fg=yellow>Active:</>   " . ($template->is_active ? '<fg=green>Yes</>' : '<fg=red>No</>'));
        $this->line("<fg=yellow>Variables:</> {" . implode('}, {', $template->extractVariables()) . '}');
        $this->newLine();
        $this->line('<fg=cyan>Raw body:</>');
        $this->line('  ' . $template->body);

        // ── Demo preview ──────────────────────────────────────────────────────
        $demoBody  = $template->previewBody();
        $demoChars = mb_strlen($demoBody);
        $demoPages = (int) ceil($demoChars / 153);

        $this->newLine();
        $this->line('<fg=green>Demo preview (sample values):</>');
        $this->line('  ' . $demoBody);
        $this->line("  <fg=yellow>Length: {$demoChars} chars · {$demoPages} SMS page(s)</>");

        if ($demoChars > 160) {
            $this->warn("  ⚠ This template spans {$demoPages} SMS pages — may incur higher billing.");
        }

        // ── --context override ────────────────────────────────────────────────
        $contextJson = $this->option('context');
        if ($contextJson) {
            $overrideContext = json_decode($contextJson, true);
            if (!is_array($overrideContext)) {
                $this->error('--context must be valid JSON, e.g. --context=\'{"first_name":"Mwansa"}\'');
                return self::FAILURE;
            }

            $overrideBody  = $this->service->renderTemplate($triggerKey, $overrideContext);
            $overrideChars = mb_strlen($overrideBody);

            $this->newLine();
            $this->line('<fg=green>Preview with --context overrides:</>');
            $this->line('  ' . $overrideBody);
            $this->line("  <fg=yellow>Length: {$overrideChars} chars</>");
        }

        // ── Live loan preview ─────────────────────────────────────────────────
        $loanArg = $this->argument('loan');

        if (!$loanArg && !$contextJson) {
            if (!$this->confirm('Preview with a real loan from the database?', false)) {
                return self::SUCCESS;
            }
            $loanArg = $this->ask('Enter loan ID or loan_number');
        }

        if (!$loanArg) {
            return self::SUCCESS;
        }

        $loan = ctype_digit((string) $loanArg)
            ? Loan::find((int) $loanArg)
            : Loan::where('loan_number', $loanArg)->first();

        if (!$loan) {
            $this->error("Loan '{$loanArg}' not found.");
            return self::FAILURE;
        }

        $loan->load([
            'borrower',
            'loanProduct',
            'loanBalance',
            'loanSchedule' => fn ($q) => $q
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->orderBy('due_date')
                ->limit(1),
            'penalties' => fn ($q) => $q->where('status', 'outstanding'),
            'appliedBy:id,name,phone',
        ]);

        $schedule = $loan->loanSchedule->first();
        $context  = $this->service->buildContext($loan, $schedule);

        // Apply --context overrides on top of live data
        if ($contextJson && !empty($overrideContext)) {
            $context = array_merge($context, $overrideContext);
        }

        $liveBody  = $this->service->renderTemplate($triggerKey, $context);
        $liveChars = mb_strlen($liveBody);
        $livePages = (int) ceil($liveChars / 153);

        $borrowerName = $loan->borrower?->full_name ?? 'Unknown';
        $this->newLine();
        $this->line(
            "<fg=green>Live preview — Loan: {$loan->loan_number} ({$borrowerName}):</>"
        );
        $this->line('  ' . $liveBody);
        $this->line("  <fg=yellow>Length: {$liveChars} chars · {$livePages} SMS page(s)</>");

        // Warn about any unresolved {tokens}
        if (preg_match('/\{[a-z_]+\}/', $liveBody)) {
            $this->warn('  ⚠ Some variables could not be resolved with this loan\'s data.');
        }

        // ── --send mode ───────────────────────────────────────────────────────
        if ($this->option('send')) {
            $phone = $loan->borrower?->phone_primary;

            if (!$phone) {
                $this->error('Borrower has no phone number — cannot send.');
                return self::FAILURE;
            }

            $this->newLine();

            if ($this->smsDriver->isSandbox()) {
                $this->warn('AT_SANDBOX=true — this will send to the AT sandbox (no real SMS delivered).');
            }

            if (!$this->confirm("Send this SMS to {$phone}?", false)) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }

            $result = $this->service->sendRaw($phone, $liveBody);

            if ($result['status'] === 'sent') {
                $this->info("✓ SMS sent.");
                $this->line("  Provider ref: {$result['provider_ref']}");
                $this->line("  Cost:         {$result['cost']}");
            } else {
                $this->error("✗ Send failed: {$result['error']}");
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function renderTemplateList(): void
    {
        $templates = SmsTemplate::orderBy('category')->orderBy('trigger_key')->get();

        if ($templates->isEmpty()) {
            $this->warn('No SMS templates found. Run: php artisan db:seed --class=SmsTemplateSeeder');
            return;
        }

        $this->table(
            ['Trigger Key', 'Name', 'Category', 'Active', 'Chars', 'Pages', 'Last Updated'],
            $templates->map(fn ($t) => [
                $t->trigger_key,
                $t->name,
                $t->category,
                $t->is_active ? '✓' : '✗',
                $t->char_count,
                $t->sms_pages,
                optional($t->updated_at)->format('d M Y'),
            ])->toArray()
        );
    }
}
