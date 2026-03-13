<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Penalty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/reports/portfolio
    // Consolidated portfolio KPIs and product breakdown
    // Query params: date_from, date_to, officer_id
    // ──────────────────────────────────────────────────────────────────────────
    public function portfolio(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        // Portfolio KPIs
        $activeLoans = Loan::active()->with('loanBalance')->get();
        $kpis = [
            'total_active_loans'     => $activeLoans->count(),
            'total_portfolio_value'  => $activeLoans->sum(fn ($l) => $l->loanBalance?->principal_balance ?? 0),
            'total_outstanding'      => $activeLoans->sum(fn ($l) => $l->loanBalance?->total_outstanding ?? 0),
            'overdue_loans'          => Loan::overdue()->count(),
            'loans_closed_in_period' => Loan::whereBetween('updated_at', [$dateFrom, $dateTo])->where('status', 'closed')->count(),
            'total_disbursed_period' => Loan::whereBetween('disbursed_at', [$dateFrom, $dateTo])->sum('principal_amount'),
            'total_collected_period' => Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount_received'),
            'penalties_outstanding'  => Penalty::outstanding()->sum('penalty_amount'),
        ];

        // PAR ratios — days overdue derived from earliest overdue instalment in loan_schedule
        $portfolioValue = $kpis['total_outstanding'] > 0 ? $kpis['total_outstanding'] : 1;
        $overdueAmounts = DB::table('loans')
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->leftJoin(DB::raw('(SELECT loan_id, DATEDIFF(NOW(), MIN(due_date)) as days_overdue FROM loan_schedule WHERE status = "overdue" GROUP BY loan_id) as ls_days'), 'loans.id', '=', 'ls_days.loan_id')
            ->where('loans.status', 'overdue')
            ->selectRaw('
                SUM(CASE WHEN COALESCE(ls_days.days_overdue,0) >= 30  THEN loan_balances.total_outstanding ELSE 0 END) as par30,
                SUM(CASE WHEN COALESCE(ls_days.days_overdue,0) >= 60  THEN loan_balances.total_outstanding ELSE 0 END) as par60,
                SUM(CASE WHEN COALESCE(ls_days.days_overdue,0) >= 90  THEN loan_balances.total_outstanding ELSE 0 END) as par90
            ')
            ->first();

        $kpis['par_30']        = round(($overdueAmounts->par30 ?? 0) / $portfolioValue * 100, 2);
        $kpis['par_60']        = round(($overdueAmounts->par60 ?? 0) / $portfolioValue * 100, 2);
        $kpis['par_90']        = round(($overdueAmounts->par90 ?? 0) / $portfolioValue * 100, 2);
        $kpis['par30_amount']  = (float) ($overdueAmounts->par30 ?? 0);
        $kpis['par60_amount']  = (float) ($overdueAmounts->par60 ?? 0);
        $kpis['par90_amount']  = (float) ($overdueAmounts->par90 ?? 0);
        $kpis['avg_loan_size'] = $kpis['total_active_loans'] > 0
            ? round($kpis['total_outstanding'] / $kpis['total_active_loans'], 2)
            : 0;

        // Product breakdown
        $byProduct = DB::table('loans')
            ->join('loan_products', 'loans.loan_product_id', '=', 'loan_products.id')
            ->leftJoin('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->where('loans.status', '!=', 'rejected')
            ->selectRaw('
                loan_products.id,
                loan_products.name,
                COUNT(DISTINCT loans.id) as total_loans,
                SUM(CASE WHEN loans.status IN ("active","overdue") THEN 1 ELSE 0 END) as active_loans,
                SUM(loans.principal_amount) as total_disbursed,
                SUM(CASE WHEN loans.status IN ("active","overdue") THEN loan_balances.total_outstanding ELSE 0 END) as outstanding,
                SUM(CASE WHEN loans.status = "overdue" THEN 1 ELSE 0 END) as overdue_count,
                AVG(loans.term_months) as avg_term,
                (SELECT COALESCE(SUM(p2.amount_received),0) FROM payments p2
                    JOIN loans l2 ON p2.loan_id = l2.id
                    WHERE l2.loan_product_id = loan_products.id
                    AND p2.payment_date >= DATE_FORMAT(NOW(), "%Y-%m-01")) as collected_mtd
            ')
            ->groupBy('loan_products.id', 'loan_products.name')
            ->get();

        // Monthly disbursements chart data (last 12 months)
        $monthly = DB::table('loans')
            ->whereNotNull('disbursed_at')
            ->whereBetween('disbursed_at', [now()->subMonths(12), now()])
            ->selectRaw('DATE_FORMAT(disbursed_at, "%Y-%m") as month, SUM(principal_amount) as disbursed')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyPayments = DB::table('payments')
            ->whereBetween('payment_date', [now()->subMonths(12), now()])
            ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, SUM(amount_received) as collected')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chartData = $monthly->map(fn ($row) => [
            'month'      => $row->month,
            'disbursed'  => (float) $row->disbursed,
            'collected'  => (float) ($monthlyPayments[$row->month]->collected ?? 0),
        ]);

        return response()->json([
            'period'       => ['from' => $dateFrom, 'to' => $dateTo],
            'kpis'         => $kpis,
            'by_product'   => $byProduct,
            'monthly_chart'=> $chartData,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/reports/collections
    // Payment receipts and officer performance for a period
    // ──────────────────────────────────────────────────────────────────────────
    public function collections(Request $request): JsonResponse
    {
        $request->validate([
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date|after_or_equal:date_from',
            'officer_id' => 'nullable|exists:users,id',
        ]);

        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        $paymentsQuery = Payment::with([
            'loan:id,loan_number,borrower_id,loan_product_id',
            'loan.borrower:id,first_name,last_name',
            'loan.loanProduct:id,name',
            'recordedBy:id,name',
            'paymentAllocations',
        ])
        ->whereBetween('payment_date', [$dateFrom, $dateTo])
        ->orderByDesc('payment_date');

        if ($request->officer_id) {
            $paymentsQuery->where('recorded_by', $request->officer_id);
        }

        // Officer performance breakdown
        $officerPerf = DB::table('payments')
            ->join('users', 'payments.recorded_by', '=', 'users.id')
            ->whereBetween('payments.payment_date', [$dateFrom, $dateTo])
            ->selectRaw('
                users.id,
                users.name,
                users.role,
                COUNT(DISTINCT payments.loan_id) as unique_loans,
                COUNT(payments.id) as receipt_count,
                SUM(payments.amount_received) as total_collected,
                SUM(payments.towards_principal) as total_principal,
                SUM(payments.towards_interest) as total_interest,
                SUM(payments.towards_penalty) as total_penalty,
                (SELECT COUNT(*) FROM loans
                    WHERE loans.applied_by = users.id
                    AND loans.status = "overdue") as overdue_count
            ')
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderByDesc('total_collected')
            ->get();

        // Period totals — full period breakdown for tfoot
        $paymentTotals = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->when($request->officer_id, fn ($q) => $q->where('recorded_by', $request->officer_id))
            ->selectRaw('
                COUNT(*) as receipt_count,
                SUM(amount_received) as total_collected,
                SUM(towards_principal) as total_principal,
                SUM(towards_interest) as total_interest,
                SUM(towards_penalty) as total_penalty
            ')
            ->first();

        return response()->json([
            'period'          => ['from' => $dateFrom, 'to' => $dateTo],
            'payment_totals'  => $paymentTotals,
            'officer_perf'    => $officerPerf,
            'payments'        => $paymentsQuery->paginate(50),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/reports/aging
    // Instalment aging breakdown and PAR analysis
    // ──────────────────────────────────────────────────────────────────────────
    public function aging(Request $request): JsonResponse
    {
        $asOf = $request->as_of ?? today()->toDateString();

        $aging = DB::table('loan_schedule')
            ->join('loans', 'loan_schedule.loan_id', '=', 'loans.id')
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->whereIn('loan_schedule.status', ['overdue', 'partial'])
            ->whereIn('loans.status', ['active', 'overdue'])
            ->selectRaw("
                CASE
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 1  AND 7   THEN '1_7'
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 8  AND 30  THEN '8_30'
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 31 AND 60  THEN '31_60'
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 61 AND 90  THEN '61_90'
                    ELSE '90+'
                END as bucket,
                COUNT(DISTINCT loans.id) as loans,
                COUNT(loan_schedule.id) as instalments,
                SUM(loan_schedule.principal_portion) as principal_due,
                SUM(loan_schedule.interest_portion)  as interest_due
            ")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        // Add penalty totals per bucket
        $penalties = DB::table('penalties')
            ->join('loan_schedule', 'penalties.loan_schedule_id', '=', 'loan_schedule.id')
            ->where('penalties.status', 'outstanding')
            ->selectRaw("
                CASE
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 1  AND 7   THEN '1_7'
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 8  AND 30  THEN '8_30'
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 31 AND 60  THEN '31_60'
                    WHEN DATEDIFF('{$asOf}', loan_schedule.due_date) BETWEEN 61 AND 90  THEN '61_90'
                    ELSE '90+'
                END as bucket,
                SUM(penalties.penalty_amount) as penalties
            ")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $bucketOrder = ['1_7', '8_30', '31_60', '61_90', '90+'];
        $rows = collect($bucketOrder)->map(fn ($b) => [
            'bucket'        => $b,
            'loans'         => (int) ($aging[$b]->loans ?? 0),
            'instalments'   => (int) ($aging[$b]->instalments ?? 0),
            'principal_due' => (float) ($aging[$b]->principal_due ?? 0),
            'interest_due'  => (float) ($aging[$b]->interest_due ?? 0),
            'penalties'     => (float) ($penalties[$b]->penalties ?? 0),
            'total'         => (float) (($aging[$b]->principal_due ?? 0) + ($aging[$b]->interest_due ?? 0) + ($penalties[$b]->penalties ?? 0)),
        ]);

        // PAR trend (last 8 months)
        $trend = collect(range(7, 0))->map(function ($monthsAgo) {
            $date     = now()->subMonths($monthsAgo)->endOfMonth()->toDateString();
            $portfolio = DB::table('loans')
                ->where('status', '!=', 'rejected')
                ->whereDate('disbursed_at', '<=', $date)
                ->sum('principal_amount');

            $par30Amount = DB::table('loan_balances')
                ->join('loans', 'loan_balances.loan_id', '=', 'loans.id')
                ->join(DB::raw('(SELECT loan_id, DATEDIFF(NOW(), MIN(due_date)) as days_overdue FROM loan_schedule WHERE status = "overdue" GROUP BY loan_id) as ls_days'), 'loans.id', '=', 'ls_days.loan_id')
                ->where('loans.status', 'overdue')
                ->where('ls_days.days_overdue', '>=', 30)
                ->sum('loan_balances.total_outstanding');

            return [
                'month' => now()->subMonths($monthsAgo)->format('M'),
                'par30' => $portfolio > 0 ? round($par30Amount / $portfolio * 100, 2) : 0,
            ];
        });

        return response()->json([
            'as_of'     => $asOf,
            'aging'     => $rows,
            'par_trend' => $trend,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/reports/statement/{loan}
    // Official loan account statement
    // ──────────────────────────────────────────────────────────────────────────
    public function statement(Loan $loan): JsonResponse
    {
        $loan->load([
            'borrower',
            'loanProduct:id,name',
            'loanBalance',
            'loanSchedule',
            'payments.paymentAllocations',
            'penalties',
            'appliedBy:id,name',
        ]);

        // Build interleaved transaction history
        $transactions = collect();

        // Opening entry (disbursement)
        $transactions->push([
            'ref'         => 'DISB-' . $loan->id,
            'date'        => $loan->disbursed_at?->toDateString(),
            'description' => 'Loan Disbursement',
            'principal'   => null,
            'interest'    => null,
            'penalty'     => null,
            'paid'        => null,
            'balance'     => (float) $loan->total_repayable,
        ]);

        // Payments
        $runningBalance = (float) $loan->total_repayable;
        foreach ($loan->payments->sortBy('payment_date') as $payment) {
            $runningBalance -= $payment->amount_received;
            $allocated = $payment->paymentAllocations;
            $transactions->push([
                'ref'         => $payment->receipt_number,
                'date'        => $payment->payment_date->toDateString(),
                'description' => 'Payment received — ' . ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'principal'   => (float) $payment->towards_principal,
                'interest'    => (float) $payment->towards_interest,
                'penalty'     => (float) $payment->towards_penalty,
                'paid'        => (float) $payment->amount_received,
                'balance'     => round($runningBalance, 2),
            ]);
        }

        // Penalties
        foreach ($loan->penalties->sortBy('applied_date') as $penalty) {
            if ($penalty->status === 'outstanding') {
                $runningBalance += $penalty->penalty_amount;
            }
            $transactions->push([
                'ref'         => 'PEN-' . $penalty->id,
                'date'        => $penalty->applied_date->toDateString(),
                'description' => $penalty->status === 'waived'
                    ? "Penalty waived — {$penalty->waiver_reason}"
                    : 'Penalty applied',
                'principal'   => null,
                'interest'    => null,
                'penalty'     => $penalty->status === 'waived' ? -(float) $penalty->penalty_amount : (float) $penalty->penalty_amount,
                'paid'        => null,
                'balance'     => round($runningBalance, 2),
            ]);
        }

        $transactions = $transactions->sortBy('date')->values();

        // Statement summary
        $summary = [
            'principal'       => (float) $loan->principal_amount,
            'total_repayable' => (float) $loan->total_repayable,
            'total_paid'      => (float) $loan->payments->sum('amount_received'),
            'balance_due'     => (float) ($loan->loanBalance?->total_outstanding ?? 0),
        ];

        return response()->json([
            'statement_number' => 'STM-' . str_pad($loan->id, 5, '0', STR_PAD_LEFT),
            'generated_at'     => now()->toDateTimeString(),
            'loan'             => $loan->only('loan_number', 'principal_amount', 'interest_rate', 'term_months', 'disbursed_at', 'maturity_date', 'status'),
            'borrower'         => $loan->borrower->only('borrower_number', 'first_name', 'last_name', 'nrc_number', 'phone_primary', 'residential_address'),
            'product'          => $loan->loanProduct->only('id', 'name'),
            'officer'          => $loan->appliedBy->only('id', 'name'),
            'summary'          => $summary,
            'transactions'     => $transactions,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/reports/loan-book
    // Full active loan register
    // Query params: officer_id, loan_product_id, status, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function loanBook(Request $request): JsonResponse
    {
        $query = Loan::with([
            'borrower:id,borrower_number,first_name,last_name,phone_primary',
            'loanProduct:id,name,code',
            'collateralAsset:id,vehicle_registration,vehicle_make,vehicle_model,asset_type',
            'loanBalance',
            'appliedBy:id,name',
        ])
        ->whereNotIn('status', ['rejected', 'pending_approval'])
        ->orderBy('loan_number');

        if ($request->officer_id) {
            $query->where('applied_by', $request->officer_id);
        }

        if ($request->loan_product_id) {
            $query->where('loan_product_id', $request->loan_product_id);
        }

        if ($request->status) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // Totals row (without pagination)
        $totals = DB::table('loans')
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->whereNotIn('loans.status', ['rejected', 'pending_approval'])
            ->selectRaw('
                COUNT(*) as loan_count,
                SUM(loans.principal_amount) as total_principal,
                AVG(loans.interest_rate) as avg_rate,
                AVG(loans.term_months) as avg_term,
                SUM(loan_balances.total_outstanding) as total_outstanding,
                SUM(loans.monthly_instalment) as total_monthly_pmt
            ')
            ->first();

        return response()->json([
            'as_of'  => today()->toDateString(),
            'totals' => $totals,
            'loans'  => $query->paginate($request->per_page ?? 50),
        ]);
    }
}
