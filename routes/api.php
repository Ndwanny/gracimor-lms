<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CollateralController;
use App\Http\Controllers\Api\GuarantorController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\LoanProductController;
use App\Http\Controllers\Api\OverdueController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PenaltyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SmsTemplateController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Gracimor LMS — API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (set in bootstrap/app.php).
|
| Role middleware:
|   role:superadmin,ceo,manager  →  senior access
|   role:superadmin,ceo          →  executive-only
|   role:superadmin              →  system admin only
|
| Auth middleware: auth:sanctum  (Laravel Sanctum token-based auth)
|
*/


// ═══════════════════════════════════════════════════════════════════════════════
// PUBLIC — No authentication required
// ═══════════════════════════════════════════════════════════════════════════════
Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login']);
});

// CSV import templates — public so browser <a href> links work without Bearer token
// Templates contain no sensitive data; they are blank example files only
Route::get('import/templates/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');


// ═══════════════════════════════════════════════════════════════════════════════
// AUTHENTICATED — All routes below require a valid Sanctum token
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth:sanctum', 'active.user'])->group(function () {

    // ── Auth ─────────────────────────────────────────────────────────────────
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);


    // ── Global Stats — single endpoint used by all pages ─────────────────────
    Route::get('stats', function () {
        $monthStart = now()->startOfMonth();

        // Borrowers
        $totalBorrowers = \App\Models\Borrower::count();
        $withActive     = \App\Models\Borrower::whereHas('loans', fn ($q) => $q->active())->count();
        $withOverdue    = \App\Models\Borrower::whereHas('loans', fn ($q) => $q->overdue())->count();

        // Loans
        $totalLoans   = \App\Models\Loan::count();
        $activeLoans  = \App\Models\Loan::active()->count();
        $pendingLoans = \App\Models\Loan::whereIn('status', ['pending', 'pending_approval'])->count();
        $overdueLoans = \App\Models\Loan::overdue()->count();
        $closedLoans  = \App\Models\Loan::where('status', 'closed')->count();

        // Payments this month
        $monthPay       = \App\Models\Payment::whereDate('payment_date', '>=', $monthStart);
        $todayPay       = \App\Models\Payment::whereDate('payment_date', today());
        $instalment     = (clone $monthPay)->where('payment_type', 'instalment')->count();
        $partial        = (clone $monthPay)->where('payment_type', 'partial')->count();
        $earlySettle    = (clone $monthPay)->where('payment_type', 'early_settlement')->count();
        $penaltyPay     = (clone $monthPay)->where('payment_type', 'penalty')->count();

        // Portfolio & overdue financials
        $portfolioValue      = \App\Models\Loan::active()
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->sum('loan_balances.total_outstanding');
        $totalArrears        = \App\Models\LoanBalance::whereHas('loan', fn ($q) => $q->overdue())
            ->sum('total_outstanding');
        $penaltiesOutstanding = \App\Models\LoanBalance::sum('penalty_outstanding');
        $monthCollections    = (clone $monthPay)->sum('amount_received');
        $todayCollections    = (clone $todayPay)->sum('amount_received');
        $todayPayCount       = (clone $todayPay)->count();
        $totalPayCount       = \App\Models\Payment::count();
        $pendingAmount       = \App\Models\Loan::whereIn('status', ['pending', 'pending_approval'])->sum('principal_amount');
        $closedMonthAmount   = \App\Models\Loan::where('status', 'closed')->whereDate('updated_at', '>=', $monthStart)->sum('principal_amount');

        // Due today — unpaid instalments with due_date = today
        $dueTodayQ        = \App\Models\LoanSchedule::whereDate('due_date', today())
            ->whereNotIn('status', ['paid'])
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']));
        $dueTodayCount    = (clone $dueTodayQ)->count();
        $dueTodayExpected = (clone $dueTodayQ)->sum('total_due');

        // Past due — unpaid instalments whose due_date has already passed
        $pastDueQ        = \App\Models\LoanSchedule::whereDate('due_date', '<', today())
            ->whereNotIn('status', ['paid'])
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']));
        $pastDueCount    = (clone $pastDueQ)->count();
        $pastDueExpected = (clone $pastDueQ)->sum('total_due');

        return response()->json([
            'borrowers' => [
                'total'          => $totalBorrowers,
                'with_active'    => $withActive,
                'with_overdue'   => $withOverdue,
                'no_active_loan' => $totalBorrowers - $withActive,
            ],
            'loans' => [
                'total'   => $totalLoans,
                'active'  => $activeLoans,
                'pending' => $pendingLoans,
                'overdue' => $overdueLoans,
                'closed'  => $closedLoans,
            ],
            'payments' => [
                'month_count'      => (clone $monthPay)->count(),
                'month_total'      => $monthCollections,
                'today_total'      => $todayCollections,
                'today_count'      => $todayPayCount,
                'total_count'      => $totalPayCount,
                'instalment'       => $instalment,
                'partial'          => $partial,
                'early_settlement' => $earlySettle,
                'penalty'          => $penaltyPay,
            ],
            'overdue' => [
                'total_loans'            => $overdueLoans,
                'total_arrears'          => $totalArrears,
                'penalties_outstanding'  => $penaltiesOutstanding,
                'month_collections'      => $monthCollections,
            ],
            'portfolio_value'    => $portfolioValue,
            'pending_amount'     => $pendingAmount,
            'closed_month_amount'=> $closedMonthAmount,
            'due_today' => [
                'count'    => $dueTodayCount,
                'expected' => round($dueTodayExpected, 2),
            ],
            'past_due' => [
                'count'    => $pastDueCount,
                'expected' => round($pastDueExpected, 2),
            ],
        ]);
    });

    // ── Dashboard Due Instalments (today + past-due unpaid) ──────────────────
    Route::get('dashboard/due-instalments', function () {
        $rows = \App\Models\LoanSchedule::whereDate('due_date', '<=', today())
            ->whereNotIn('status', ['paid'])
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']))
            ->with(['loan:id,loan_number,monthly_instalment', 'loan.borrower:id,first_name,last_name'])
            ->orderBy('due_date', 'asc')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
                'id'           => $s->id,
                'loan_number'  => $s->loan?->loan_number,
                'borrower'     => trim(($s->loan?->borrower?->first_name ?? '') . ' ' . ($s->loan?->borrower?->last_name ?? '')),
                'due_date'     => $s->due_date,
                'total_due'    => $s->total_due,
                'status'       => $s->status,
                'days_overdue' => now()->diffInDays(\Carbon\Carbon::parse($s->due_date), false) * -1,
            ]);
        return response()->json($rows);
    });

    // ── Dashboard Collections Chart ───────────────────────────────────────────
    Route::get('dashboard/collections', function (\Illuminate\Http\Request $request) {
        $period = $request->get('period', 'week');

        if ($period === 'month') {
            $weeks = collect(range(3, 0))->map(fn ($i) => [
                'start' => now()->startOfWeek()->subWeeks($i)->toDateString(),
                'end'   => now()->startOfWeek()->subWeeks($i)->endOfWeek()->toDateString(),
                'label' => 'Wk ' . (4 - $i),
            ]);
            $labels = $weeks->pluck('label')->toArray();
            $exp = $weeks->map(fn ($w) => (float) \App\Models\LoanSchedule::whereBetween('due_date', [$w['start'], $w['end']])->sum('total_due'))->toArray();
            $col = $weeks->map(fn ($w) => (float) \App\Models\Payment::whereBetween('payment_date', [$w['start'], $w['end']])->sum('amount_received'))->toArray();
        } elseif ($period === 'quarter') {
            $months = collect(range(2, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
            $labels = $months->map(fn ($m) => $m->format('M'))->toArray();
            $exp = $months->map(fn ($m) => (float) \App\Models\LoanSchedule::whereYear('due_date', $m->year)->whereMonth('due_date', $m->month)->sum('total_due'))->toArray();
            $col = $months->map(fn ($m) => (float) \App\Models\Payment::whereYear('payment_date', $m->year)->whereMonth('payment_date', $m->month)->sum('amount_received'))->toArray();
        } else {
            // week — last 7 days
            $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));
            $labels = $days->map(fn ($d) => $d->format('D'))->toArray();
            $exp = $days->map(fn ($d) => (float) \App\Models\LoanSchedule::whereDate('due_date', $d)->sum('total_due'))->toArray();
            $col = $days->map(fn ($d) => (float) \App\Models\Payment::whereDate('payment_date', $d)->sum('amount_received'))->toArray();
        }

        return response()->json(compact('labels', 'exp', 'col'));
    });

    // ── Dashboard / Global Stats (legacy — kept for backward compat) ──────────
    Route::get('dashboard/stats', function () {
        return response()->json([
            'active_loans'     => \App\Models\Loan::active()->count(),
            'overdue_loans'    => \App\Models\Loan::overdue()->count(),
            'pending_approval' => \App\Models\Loan::whereIn('status', ['pending', 'pending_approval'])->count(),
            'collections_today'=> \App\Models\Payment::whereDate('payment_date', today())->sum('amount_received'),
            'portfolio_value'  => \App\Models\Loan::active()
                ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
                ->sum('loan_balances.total_outstanding'),
        ]);
    });


    // ── Borrowers ─────────────────────────────────────────────────────────────
    Route::prefix('borrowers')->name('borrowers.')->group(function () {
        Route::get('/',                          [BorrowerController::class, 'index'])->name('index');
        Route::post('/',                         [BorrowerController::class, 'store'])->name('store');
        Route::get('{borrower}',                 [BorrowerController::class, 'show'])->name('show');
        Route::put('{borrower}',                 [BorrowerController::class, 'update'])->name('update');
        Route::delete('{borrower}',              [BorrowerController::class, 'destroy'])->name('destroy')
             ->middleware('role:superadmin,ceo,manager');

        // KYC
        Route::post('{borrower}/verify-kyc',     [BorrowerController::class, 'verifyKyc'])->name('verify-kyc')
             ->middleware('role:superadmin,ceo,manager,officer');

        // Documents
        Route::post('{borrower}/documents',                          [BorrowerController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('{borrower}/documents/{document}/download',       [BorrowerController::class, 'downloadDocument'])->name('documents.download');
        Route::get('{borrower}/documents/{document}/view',           [BorrowerController::class, 'viewDocument'])->name('documents.view');

        // Borrower statement (all loans summary)
        Route::get('{borrower}/statement',       [BorrowerController::class, 'statement'])->name('statement');
    });


    // ── Loans ─────────────────────────────────────────────────────────────────
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/',                          [LoanController::class, 'index'])->name('index');
        Route::post('/',                         [LoanController::class, 'store'])->name('store');

        // Calculator preview — no loan created, no auth restriction
        Route::post('calculate',                 [LoanController::class, 'calculate'])->name('calculate');

        Route::get('{loan}',                     [LoanController::class, 'show'])->name('show');
        Route::get('{loan}/schedule',            [LoanController::class, 'schedule'])->name('schedule');
        Route::get('{loan}/activity',            [LoanController::class, 'activity'])->name('activity');

        // Workflow transitions — require senior roles
        Route::post('{loan}/approve',            [LoanController::class, 'approve'])->name('approve')
             ->middleware('role:superadmin,ceo,manager,officer');
        Route::post('{loan}/reject',             [LoanController::class, 'reject'])->name('reject')
             ->middleware('role:superadmin,ceo,manager,officer');
        Route::post('{loan}/disburse',           [LoanController::class, 'disburse'])->name('disburse')
             ->middleware('role:superadmin,ceo,manager,officer');
        Route::post('{loan}/early-settle',       [LoanController::class, 'earlySettle'])->name('early-settle');
        Route::post('{loan}/write-off',          [LoanController::class, 'writeOff'])->name('write-off')
             ->middleware('role:superadmin,ceo,manager,officer');

        // Manual reminder
        Route::post('{loan}/send-reminder',      [LoanController::class, 'sendReminder'])->name('send-reminder');

        // Agreement e-signatures
        Route::post('{loan}/signatures',         [LoanController::class, 'saveSignature'])->name('signatures.save');
        Route::delete('{loan}/signatures/{role}',[LoanController::class, 'clearSignature'])->name('signatures.clear');

        // Guarantors (nested under loans)
        Route::get('{loan}/guarantors',          [GuarantorController::class, 'index'])->name('guarantors.index');
        Route::post('{loan}/guarantors',         [GuarantorController::class, 'store'])->name('guarantors.store');
        Route::delete('{loan}/guarantors/{guarantor}', [GuarantorController::class, 'destroy'])->name('guarantors.destroy');
    });


    // ── Payments ──────────────────────────────────────────────────────────────
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                          [PaymentController::class, 'index'])->name('index');
        Route::post('/',                         [PaymentController::class, 'store'])->name('store');
        Route::get('summary',                    [PaymentController::class, 'summary'])->name('summary');
        Route::get('{payment}',                  [PaymentController::class, 'show'])->name('show');
        Route::get('{payment}/receipt',          [PaymentController::class, 'receipt'])->name('receipt');

        // Reversal — same-day only, manager+
        Route::delete('{payment}',               [PaymentController::class, 'destroy'])->name('destroy')
             ->middleware('role:superadmin,ceo,manager');
    });


    // ── Penalties ─────────────────────────────────────────────────────────────
    Route::prefix('penalties')->name('penalties.')->group(function () {
        Route::get('/',                          [PenaltyController::class, 'index'])->name('index');
        Route::get('accrual-preview',            [PenaltyController::class, 'accrualPreview'])->name('accrual-preview');
        Route::get('{penalty}',                  [PenaltyController::class, 'show'])->name('show');

        // Penalty actions — manager+
        Route::post('apply-daily',               [PenaltyController::class, 'applyDaily'])->name('apply-daily')
             ->middleware('role:superadmin,ceo,manager');
        Route::post('waive',                     [PenaltyController::class, 'waive'])->name('waive')
             ->middleware('role:superadmin,ceo,manager,officer');
        Route::post('bulk-waive',                [PenaltyController::class, 'bulkWaive'])->name('bulk-waive')
             ->middleware('role:superadmin,ceo');
    });


    // ── Collateral ────────────────────────────────────────────────────────────
    Route::prefix('collateral')->name('collateral.')->group(function () {
        Route::get('/',                          [CollateralController::class, 'index'])->name('index');
        Route::post('/',                         [CollateralController::class, 'store'])->name('store');
        Route::get('{collateralAsset}',          [CollateralController::class, 'show'])->name('show');
        Route::put('{collateralAsset}',          [CollateralController::class, 'update'])->name('update');
        Route::post('{collateralAsset}/revalue', [CollateralController::class, 'revalue'])->name('revalue')
             ->middleware('role:superadmin,ceo,manager');
    });


    // ── Overdue / Collections Workflow ────────────────────────────────────────
    Route::prefix('overdue')->name('overdue.')->group(function () {
        Route::get('stats',                      [OverdueController::class, 'stats'])->name('stats');
        Route::get('loans',                      [OverdueController::class, 'loans'])->name('loans');
        Route::get('loans/{loan}',               [OverdueController::class, 'loanDetail'])->name('loan-detail');
        Route::get('collections-queue',          [OverdueController::class, 'collectionsQueue'])->name('collections-queue');
        Route::post('log-contact',               [OverdueController::class, 'logContact'])->name('log-contact');
        Route::post('escalate/{loan}',           [OverdueController::class, 'escalate'])->name('escalate')
             ->middleware('role:superadmin,ceo,manager,officer');
    });


    // ── Loan Products (Settings) ──────────────────────────────────────────────
    // Read access for all; writes restricted to senior roles
    Route::prefix('loan-products')->name('loan-products.')->group(function () {
        Route::get('/',                          [LoanProductController::class, 'index'])->name('index');
        Route::get('{loanProduct}',              [LoanProductController::class, 'show'])->name('show');

        Route::post('/',                         [LoanProductController::class, 'store'])->name('store')
             ->middleware('role:superadmin,ceo');
        Route::put('{loanProduct}',              [LoanProductController::class, 'update'])->name('update')
             ->middleware('role:superadmin,ceo');
        Route::delete('{loanProduct}',           [LoanProductController::class, 'destroy'])->name('destroy')
             ->middleware('role:superadmin');
    });


    // ── Calendar ─────────────────────────────────────────────────────────────
    Route::get('calendar/schedule', [CalendarController::class, 'schedule'])->name('calendar.schedule');


    // ── Reports ───────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('portfolio',                  [ReportController::class, 'portfolio'])->name('portfolio');
        Route::get('collections',                [ReportController::class, 'collections'])->name('collections');
        Route::get('aging',                      [ReportController::class, 'aging'])->name('aging');
        Route::get('statement/{loan}',           [ReportController::class, 'statement'])->name('statement');
        Route::get('loan-book',                  [ReportController::class, 'loanBook'])->name('loan-book');
    });


    // ── Users (Settings) ──────────────────────────────────────────────────────
    // Self-service endpoints for any authenticated user
    Route::get('users/me',                       [UserController::class, 'me'])->name('users.me');
    Route::put('users/me/profile',               [UserController::class, 'updateProfile'])->name('users.me.profile');

    // Admin endpoints — superadmin only
    Route::middleware('role:superadmin,ceo,manager')->prefix('users')->name('users.')->group(function () {
        Route::get('/',                          [UserController::class, 'index'])->name('index');
        Route::post('/',                         [UserController::class, 'store'])->name('store');
        Route::get('{user}',                     [UserController::class, 'show'])->name('show');
        Route::put('{user}',                     [UserController::class, 'update'])->name('update');
        Route::post('{user}/deactivate',         [UserController::class, 'deactivate'])->name('deactivate');
        Route::post('{user}/reset-password',     [UserController::class, 'resetPassword'])->name('reset-password');
    });


    // ── SMS Templates ─────────────────────────────────────────────────────────
    Route::prefix('sms-templates')->name('sms-templates.')->group(function () {
        Route::get('/',                              [SmsTemplateController::class, 'index'])->name('index');
        Route::post('flush-cache',                   [SmsTemplateController::class, 'flushCache'])->name('flush-cache')
             ->middleware('role:superadmin,ceo,manager');
        Route::get('{template}',                     [SmsTemplateController::class, 'show'])->name('show');
        Route::put('{template}',                     [SmsTemplateController::class, 'update'])->name('update')
             ->middleware('role:superadmin,ceo,manager');
        Route::post('{template}/preview',            [SmsTemplateController::class, 'preview'])->name('preview');
    });


    // ── Import / Export ───────────────────────────────────────────────────
    // Imports restricted to manager+ (sensitive data operation)
    Route::middleware('role:superadmin,ceo,manager')->prefix('import')->name('import.')->group(function () {
        Route::post('borrowers',  [ImportController::class, 'importBorrowers'])->name('borrowers');
        Route::post('loans',      [ImportController::class, 'importLoans'])->name('loans');
        Route::post('payments',   [ImportController::class, 'importPayments'])->name('payments');
        Route::post('collateral', [ImportController::class, 'importCollateral'])->name('collateral');
        Route::post('guarantors', [ImportController::class, 'importGuarantors'])->name('guarantors');
    });

    // Exports — officer+
    Route::middleware('role:superadmin,ceo,manager,officer')->prefix('export')->name('export.')->group(function () {
        Route::get('borrowers', [ImportController::class, 'exportBorrowers'])->name('borrowers');
        Route::get('loans',     [ImportController::class, 'exportLoans'])->name('loans');
        Route::get('payments',  [ImportController::class, 'exportPayments'])->name('payments');
    });


    // ── Audit Log ─────────────────────────────────────────────────────────────
    Route::middleware('role:superadmin,ceo,manager,officer')->prefix('audit-log')->name('audit.')->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\AuditLog::with('user:id,name')
                ->latest()
                ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
                ->when($request->action,  fn ($q) => $q->where('action', 'like', "%{$request->action}%"))
                ->when($request->model,   fn ($q) => $q->where('auditable_type', 'like', "%{$request->model}%"))
                ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
                ->when($request->date_to,   fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

            return response()->json($query->paginate($request->per_page ?? 50));
        })->name('index');
    });

}); // end auth:sanctum
