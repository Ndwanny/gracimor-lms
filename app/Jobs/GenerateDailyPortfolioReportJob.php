<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenerateDailyPortfolioReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $today     = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();

        Log::info("[GenerateDailyPortfolioReportJob] Building daily report for {$today}");

        // Compile report data
        $data = $this->compileReportData($today, $yesterday);

        // Find recipients: CEO and all managers who are active
        $recipients = User::whereIn('role', ['ceo', 'manager'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->pluck('email', 'name');

        if ($recipients->isEmpty()) {
            Log::warning("[GenerateDailyPortfolioReportJob] No recipients found — skipping.");
            return;
        }

        // Send via Laravel Mailable
        foreach ($recipients as $name => $email) {
            Mail::to($email)->send(new \App\Mail\DailyPortfolioReport($data, $name));
        }

        Log::info("[GenerateDailyPortfolioReportJob] Report sent to {$recipients->count()} recipients.");
    }

    private function compileReportData(string $today, string $yesterday): array
    {
        $activeLoans   = Loan::active()->count();
        $overdueLoans  = Loan::overdue()->count();
        $pendingApproval = Loan::where('status', 'pending_approval')->count();

        $todayCollections = Payment::whereDate('payment_date', $today)->sum('amount');
        $newApplications  = Loan::whereDate('created_at', $yesterday)->count();

        // PAR 30
        $portfolioOutstanding = DB::table('loan_balances')
            ->join('loans', 'loans.id', '=', 'loan_balances.loan_id')
            ->whereIn('loans.status', ['active', 'overdue'])
            ->sum('total_outstanding');

        $par30Amount = DB::table('loan_balances')
            ->join('loans', 'loans.id', '=', 'loan_balances.loan_id')
            ->where('loans.status', 'overdue')
            ->where('loan_balances.days_overdue', '>=', 30)
            ->sum('total_outstanding');

        $par30 = $portfolioOutstanding > 0
            ? round($par30Amount / $portfolioOutstanding * 100, 2)
            : 0;

        // Top 5 most overdue by amount
        $topOverdue = Loan::overdue()
            ->with([
                'borrower:id,first_name,last_name,phone_primary',
                'loanBalance',
                'appliedBy:id,name',
            ])
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->orderByDesc('loan_balances.total_outstanding')
            ->select('loans.*')
            ->limit(5)
            ->get();

        return [
            'date'              => $today,
            'active_loans'      => $activeLoans,
            'overdue_loans'     => $overdueLoans,
            'pending_approval'  => $pendingApproval,
            'collections_today' => round($todayCollections, 2),
            'new_applications'  => $newApplications,
            'portfolio_value'   => round($portfolioOutstanding, 2),
            'par_30'            => $par30,
            'top_overdue'       => $topOverdue,
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[GenerateDailyPortfolioReportJob] FAILED: " . $exception->getMessage());
    }
}
