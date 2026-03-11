<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExportLoansCommand extends Command
{
    protected $signature = 'app:export-loans
        {--status=*         : Filter by status (repeatable: --status=active --status=overdue)}
        {--officer=         : Filter by applied_by user ID}
        {--product=         : Filter by loan product code}
        {--from=            : Disbursed on or after (YYYY-MM-DD)}
        {--to=              : Disbursed on or before (YYYY-MM-DD)}
        {--overdue-min=     : Only loans with days_overdue >= N}
        {--output=          : Output file path (default: storage/app/exports/loans_TIMESTAMP.csv)}
        {--include-balance  : Append live balance columns to each row}';

    protected $description = 'Export loans to CSV with flexible filters.';

    // Default columns — order determines CSV column order
    private const DEFAULT_COLUMNS = [
        'loan_number', 'status', 'disbursed_at', 'maturity_date',
        'borrower_nrc', 'borrower_name', 'borrower_phone',
        'product_code', 'product_name',
        'principal_amount', 'interest_rate', 'interest_method',
        'term_months', 'monthly_instalment',
        'total_repayable', 'total_interest', 'processing_fee',
        'disbursement_method',
        'applied_by', 'approved_by',
        'loan_purpose',
    ];

    private const BALANCE_COLUMNS = [
        'principal_balance', 'interest_balance', 'penalty_balance',
        'total_outstanding', 'total_paid',
        'days_overdue', 'instalments_overdue', 'instalments_remaining',
        'last_payment_date', 'last_payment_amount',
    ];

    public function handle(): int
    {
        $statuses      = $this->option('status') ?: [];
        $officerId     = $this->option('officer');
        $productCode   = $this->option('product');
        $from          = $this->option('from') ? Carbon::parse($this->option('from')) : null;
        $to            = $this->option('to')   ? Carbon::parse($this->option('to'))   : null;
        $overdueMin    = $this->option('overdue-min') ? (int) $this->option('overdue-min') : null;
        $inclBalance   = (bool) $this->option('include-balance');

        $outputPath = $this->option('output')
            ?: storage_path('app/exports/loans_' . now()->format('Ymd_His') . '.csv');

        $this->info('Building export query...');

        // ── Build query ───────────────────────────────────────────────────────
        $query = Loan::query()
            ->with([
                'borrower:id,first_name,last_name,nrc_number,phone_primary',
                'loanProduct:id,name,code',
                'appliedBy:id,name',
                'approvedBy:id,name',
            ]);

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if ($officerId) {
            $query->where('applied_by', $officerId);
        }

        if ($productCode) {
            $query->whereHas('loanProduct', fn ($q) => $q->where('code', $productCode));
        }

        if ($from) {
            $query->whereDate('disbursed_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('disbursed_at', '<=', $to);
        }

        if ($overdueMin !== null) {
            $query->whereHas('loanBalance', fn ($q) => $q->where('days_overdue', '>=', $overdueMin));
        }

        if ($inclBalance) {
            $query->with('loanBalance');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No loans match the given filters. Export not written.');
            return self::SUCCESS;
        }

        $this->line("  → {$total} loan(s) matched.");

        // ── Ensure output directory exists ────────────────────────────────────
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // ── Write CSV ─────────────────────────────────────────────────────────
        $handle  = fopen($outputPath, 'w');
        $columns = self::DEFAULT_COLUMNS;
        if ($inclBalance) {
            $columns = array_merge($columns, self::BALANCE_COLUMNS);
        }

        // Header row
        fputcsv($handle, $columns);

        $bar = $this->output->createProgressBar($total);
        $bar->start();
        $written = 0;

        $query->chunk(200, function ($loans) use ($handle, $columns, $inclBalance, &$written, $bar) {
            foreach ($loans as $loan) {
                fputcsv($handle, $this->buildRow($loan, $columns, $inclBalance));
                $written++;
                $bar->advance();
            }
        });

        $bar->finish();
        fclose($handle);
        $this->newLine();

        $size = round(filesize($outputPath) / 1024, 1);
        $this->info("✓ Exported {$written} rows → {$outputPath} ({$size} KB)");

        Log::info('[ExportLoans] Completed', [
            'rows'    => $written,
            'filters' => compact('statuses', 'officerId', 'productCode', 'from', 'to'),
            'output'  => $outputPath,
        ]);

        return self::SUCCESS;
    }

    private function buildRow(Loan $loan, array $columns, bool $inclBalance): array
    {
        $b    = $loan->borrower;
        $p    = $loan->loanProduct;
        $bal  = $inclBalance ? $loan->loanBalance : null;

        $map = [
            'loan_number'        => $loan->loan_number,
            'status'             => $loan->status,
            'disbursed_at'       => optional($loan->disbursed_at)->format('Y-m-d'),
            'maturity_date'      => optional($loan->maturity_date)->format('Y-m-d'),
            'borrower_nrc'       => $b?->nrc_number,
            'borrower_name'      => $b ? trim("{$b->first_name} {$b->last_name}") : '',
            'borrower_phone'     => $b?->phone_primary,
            'product_code'       => $p?->code,
            'product_name'       => $p?->name,
            'principal_amount'   => $loan->principal_amount,
            'interest_rate'      => $loan->interest_rate,
            'interest_method'    => $loan->interest_method,
            'term_months'        => $loan->term_months,
            'monthly_instalment' => $loan->monthly_instalment,
            'total_repayable'    => $loan->total_repayable,
            'total_interest'     => $loan->total_interest,
            'processing_fee'     => $loan->processing_fee,
            'disbursement_method'=> $loan->disbursement_method,
            'applied_by'         => $loan->appliedBy?->name,
            'approved_by'        => $loan->approvedBy?->name,
            'loan_purpose'       => $loan->loan_purpose,
            // Balance columns
            'principal_balance'      => $bal?->principal_balance,
            'interest_balance'       => $bal?->interest_balance,
            'penalty_balance'        => $bal?->penalty_balance,
            'total_outstanding'      => $bal?->total_outstanding,
            'total_paid'             => $bal?->total_paid,
            'days_overdue'           => $bal?->days_overdue,
            'instalments_overdue'    => $bal?->instalments_overdue,
            'instalments_remaining'  => $bal?->instalments_remaining,
            'last_payment_date'      => $bal?->last_payment_date,
            'last_payment_amount'    => $bal?->last_payment_amount,
        ];

        return array_map(fn ($col) => $map[$col] ?? '', $columns);
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// CheckSmsDeliveryCommand
// File: app/Console/Commands/CheckSmsDeliveryCommand.php
//
// Signature:  app:check-sms-delivery
// Schedule:   Not scheduled directly — dispatched per-reminder by
//             SendInstalmentRemindersCommand via CheckSmsDeliveryJob.
//             Can be run manually to batch-refresh stale statuses.
//
// Polls the Africa's Talking delivery report API for Reminder records
// whose status is still 'sent' (pending delivery confirmation).
//
// Options:
//   --hours=2     Check reminders sent within the last N hours (default: 2)
//   --limit=500   Maximum reminders to check per run
// ═══════════════════════════════════════════════════════════════════════════════
