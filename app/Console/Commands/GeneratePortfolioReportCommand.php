<?php

namespace App\Console\Commands;

use App\Mail\DailyPortfolioReport;
use App\Models\Loan;
use App\Models\LoanBalance;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GeneratePortfolioReportCommand extends Command
{
    protected $signature = 'app:generate-portfolio-report
        {--preview  : Print report to terminal without sending email}
        {--date=    : Generate as of this date (YYYY-MM-DD). Default: today}
        {--email=   : Send to this address only (overrides DAILY_REPORT_RECIPIENTS)}
        {--top=5    : Number of top overdue loans to include}';

    protected $description = 'Compile and email the daily portfolio report to senior staff.';

    public function handle(): int
    {
        $preview  = (bool) $this->option('preview');
        $asOfDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();
        $topN     = (int) ($this->option('top') ?? 5);
        $emailTo  = $this->option('email');

        $this->info("Generating portfolio report for {$asOfDate->format('d M Y')}...");
        $start = microtime(true);

        $data = $this->compileReportData($asOfDate, $topN);

        if ($preview) {
            $this->renderPreview($data);
            return self::SUCCESS;
        }

        // Resolve recipients
        $recipientList = $emailTo
            ? [$emailTo]
            : array_filter(
                array_map('trim', explode(',', config('gracimor.daily_report_recipients', ''))),
                fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
            );

        if (empty($recipientList)) {
            $this->error('No valid recipients found. Set DAILY_REPORT_RECIPIENTS in .env or use --email=.');
            return self::FAILURE;
        }

        // Send one personalised email per recipient
        $sent = 0;
        foreach ($recipientList as $email) {
            try {
                $name = $this->resolveRecipientName($email);
                Mail::to($email)->queue(new DailyPortfolioReport($data, $name));
                $this->line("  → Queued to: {$email}");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed to queue for {$email}: {$e->getMessage()}");
                Log::error('[GeneratePortfolioReport] Mail queue failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $elapsed = round(microtime(true) - $start, 2);
        $this->info("✓ Report queued for {$sent} recipient(s) in {$elapsed}s");

        Log::info('[GeneratePortfolioReport] Completed', [
            'date'       => $asOfDate->format('Y-m-d'),
            'recipients' => count($recipientList),
            'sent'       => $sent,
            'elapsed_s'  => $elapsed,
        ]);

        return self::SUCCESS;
    }

    /**
     * Compile all KPI data used by the email template.
     * Public so controllers can call it for the dashboard API endpoint.
     */
    public function compileReportData(Carbon $date, int $topN = 5): array
    {
        $today = $date->format('Y-m-d');

        return [
            'date' => $today,

            // Loan status counts
            'active_loans'     => Loan::where('status', 'active')->count(),
            'overdue_loans'    => Loan::where('status', 'overdue')->count(),
            'pending_approval' => Loan::where('status', 'pending_approval')->count(),
            'new_applications' => Loan::whereDate('created_at', $today)->count(),

            // Portfolio financial totals
            'portfolio_value'  => LoanBalance::sum('total_outstanding'),

            // Today's collections
            'collections_today' => Payment::where('status', 'paid')
                ->whereDate('payment_date', $today)
                ->sum('amount'),

            // PAR 30: loans with days_overdue >= 30 / total active portfolio
            'par_30' => $this->calcPar(30),

            // Top-N overdue loans sorted by outstanding balance
            'top_overdue' => Loan::with([
                'borrower:id,first_name,last_name,phone_primary',
                'loanBalance',
                'appliedBy:id,name',
            ])
            ->where('status', 'overdue')
            ->orderByDesc(
                LoanBalance::select('total_outstanding')
                    ->whereColumn('loan_id', 'loans.id')
                    ->limit(1)
            )
            ->limit($topN)
            ->get(),

            // Status breakdown for the bar chart in the email
            'status_breakdown' => [
                'active'           => Loan::where('status', 'active')->count(),
                'overdue'          => Loan::where('status', 'overdue')->count(),
                'closed'           => Loan::where('status', 'closed')->count(),
                'pending_approval' => Loan::where('status', 'pending_approval')->count(),
                'approved'         => Loan::where('status', 'approved')->count(),
                'written_off'      => Loan::where('status', 'written_off')->count(),
            ],
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function calcPar(int $days): float
    {
        $totalPortfolio = LoanBalance::whereHas(
            'loan', fn ($q) => $q->whereIn('status', ['active', 'overdue'])
        )->sum('total_outstanding');

        if ($totalPortfolio <= 0) {
            return 0.0;
        }

        $atRisk = LoanBalance::where('days_overdue', '>=', $days)
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']))
            ->sum('total_outstanding');

        return round(($atRisk / $totalPortfolio) * 100, 2);
    }

    private function resolveRecipientName(string $email): string
    {
        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            $parts = explode(' ', $user->name);
            return $parts[0] . (isset($parts[1]) ? ' ' . strtoupper($parts[1][0]) . '.' : '');
        }
        return 'Team';
    }

    private function renderPreview(array $data): void
    {
        $this->newLine();
        $this->line('<fg=yellow>PORTFOLIO REPORT — ' . $data['date'] . '</>');
        $this->newLine();

        $this->table(['KPI', 'Value'], [
            ['Active Loans',      $data['active_loans']],
            ['Overdue Loans',     $data['overdue_loans']],
            ['Pending Approval',  $data['pending_approval']],
            ['New Applications',  $data['new_applications']],
            ['Portfolio Value',   'K ' . number_format($data['portfolio_value'], 2)],
            ['Collections Today', 'K ' . number_format($data['collections_today'], 2)],
            ['PAR 30',            $data['par_30'] . '%'],
        ]);

        if ($data['top_overdue']->count() > 0) {
            $this->newLine();
            $this->line('<fg=red>Top Overdue Loans:</>');
            $this->table(
                ['Loan #', 'Borrower', 'Outstanding', 'Days OD'],
                $data['top_overdue']->map(fn ($l) => [
                    $l->loan_number,
                    $l->borrower?->full_name ?? '—',
                    'K ' . number_format($l->loanBalance?->total_outstanding ?? 0, 2),
                    $l->loanBalance?->days_overdue ?? 0,
                ])->toArray()
            );
        }
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// PruneAuditLogsCommand
// File: app/Console/Commands/PruneAuditLogsCommand.php
//
// Signature:  app:prune-audit-logs
// Schedule:   Daily at 00:30 ZST
//
// Deletes (or archives to a flat file) audit log entries older than
// the configured retention period. Zambian MFI regulatory guidance
// recommends 5 years for financial records; the default is 730 days (2 years)
// but is configurable per environment.
//
// Options:
//   --days=730      Retention period in days (default: 730)
//   --dry-run       Show how many rows would be deleted without deleting
//   --archive       Write rows to a gzipped JSONL file before deleting
//   --archive-path= Path to write archive files (default: storage/app/audit-archives/)
//   --chunk=1000    Rows to process per iteration (avoids lock contention)
// ═══════════════════════════════════════════════════════════════════════════════
