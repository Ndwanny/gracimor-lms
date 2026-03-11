<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\Reminder;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneAuditLogsCommand extends Command
{
    protected $signature = 'app:prune-audit-logs
        {--days=730      : Delete entries older than this many days}
        {--dry-run       : Count rows without deleting}
        {--archive       : Write to gzip archive before deleting}
        {--archive-path= : Directory for archive files (default: storage/app/audit-archives/)}
        {--chunk=1000    : Rows per delete batch}';

    protected $description = 'Archive and prune old audit log entries.';

    public function handle(): int
    {
        $days        = (int) ($this->option('days') ?? 730);
        $dryRun      = (bool) $this->option('dry-run');
        $archive     = (bool) $this->option('archive');
        $archivePath = $this->option('archive-path')
            ?: storage_path('app/audit-archives');
        $chunk       = (int) ($this->option('chunk') ?? 1000);

        $cutoff = now()->subDays($days);

        $this->info("Pruning audit logs older than {$days} days (before {$cutoff->format('d M Y')})...");

        // Count rows to be pruned
        $totalRows = \App\Models\AuditLog::where('created_at', '<', $cutoff)->count();

        if ($totalRows === 0) {
            $this->info('No audit log entries to prune.');
            return self::SUCCESS;
        }

        $this->line("  Found {$totalRows} rows to prune.");

        if ($dryRun) {
            $this->warn('DRY RUN — no rows deleted.');
            return self::SUCCESS;
        }

        // ── Optional archive step ─────────────────────────────────────────────
        if ($archive) {
            $this->archiveRows($cutoff, $archivePath);
        }

        // ── Delete in chunks ──────────────────────────────────────────────────
        $deleted = 0;
        $bar = $this->output->createProgressBar((int) ceil($totalRows / $chunk));
        $bar->start();

        do {
            $rowsDeleted = \App\Models\AuditLog::where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($chunk)
                ->delete();

            $deleted += $rowsDeleted;
            $bar->advance();
        } while ($rowsDeleted > 0);

        $bar->finish();
        $this->newLine();

        $this->info("✓ Pruned {$deleted} audit log entries.");

        Log::info('[PruneAuditLogs] Completed', [
            'deleted'     => $deleted,
            'cutoff_date' => $cutoff->format('Y-m-d'),
            'archived'    => $archive,
        ]);

        return self::SUCCESS;
    }

    private function archiveRows(Carbon $cutoff, string $archivePath): void
    {
        if (!is_dir($archivePath)) {
            mkdir($archivePath, 0755, true);
        }

        $filename   = $archivePath . '/audit_archive_'
            . now()->format('Y-m-d_His') . '.jsonl.gz';

        $this->line("  Archiving to: {$filename}");

        $gz       = gzopen($filename, 'wb9');
        $archived = 0;

        \App\Models\AuditLog::where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($gz, &$archived) {
                foreach ($rows as $row) {
                    gzwrite($gz, json_encode($row->toArray()) . "\n");
                    $archived++;
                }
            });

        gzclose($gz);

        $size = round(filesize($filename) / 1024, 1);
        $this->line("  ✓ Archived {$archived} rows to {$filename} ({$size} KB)");
    }
}
