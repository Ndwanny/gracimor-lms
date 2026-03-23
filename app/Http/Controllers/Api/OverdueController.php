<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OverdueController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/overdue/stats
    // Dashboard overview stats
    // ──────────────────────────────────────────────────────────────────────────
    public function stats(): JsonResponse
    {
        // days_overdue = days since maturity_date for true overdue loans
        $rows = Loan::overdue()
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->select(
                'loan_balances.total_outstanding',
                'loan_balances.penalty_outstanding',
                'loans.monthly_instalment',
                DB::raw('DATEDIFF(NOW(), loans.maturity_date) as days_overdue'),
            )
            ->get();

        $totalArrears   = $rows->sum('total_outstanding');
        $totalPenalties = $rows->sum('penalty_outstanding');
        // Daily accrual: 5% of monthly instalment ÷ 30 per overdue loan
        $dailyAccrual   = $rows->sum(fn ($r) => round($r->monthly_instalment * 0.05 / 30, 4));
        $avgDaysOverdue = $rows->avg('days_overdue');

        $buckets = [
            '1_7'   => $rows->filter(fn ($r) => $r->days_overdue >= 1  && $r->days_overdue <= 7)->count(),
            '8_30'  => $rows->filter(fn ($r) => $r->days_overdue >= 8  && $r->days_overdue <= 30)->count(),
            '31_60' => $rows->filter(fn ($r) => $r->days_overdue >= 31 && $r->days_overdue <= 60)->count(),
            '61_90' => $rows->filter(fn ($r) => $r->days_overdue >= 61 && $r->days_overdue <= 90)->count(),
            '90+'   => $rows->filter(fn ($r) => $r->days_overdue > 90)->count(),
        ];

        return response()->json([
            'total_overdue_loans'   => $rows->count(),
            'total_arrears'         => round($totalArrears, 2),
            'penalties_outstanding' => round($totalPenalties, 2),
            'daily_accrual_rate'    => round($dailyAccrual, 2),
            'avg_days_overdue'      => round($avgDaysOverdue, 1),
            'severity_buckets'      => $buckets,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/overdue/loans
    // Query params: search, severity, officer_id, sort_by, sort_dir, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function loans(Request $request): JsonResponse
    {
        $query = Loan::overdue()
            ->with([
                'borrower:id,borrower_number,first_name,last_name,phone_primary',
                'loanProduct:id,name',
                'loanBalance',
                'appliedBy:id,name',
            ])
            ->withCount(['penalties as outstanding_penalties_count' => fn ($q) => $q->outstanding()])
            ->withSum(['penalties as penalties_total' => fn ($q) => $q->outstanding()], 'penalty_amount')
            ->withCount(['loanSchedule as overdue_instalments_count' => fn ($q) => $q->where('status', 'overdue')]);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'like', "%$search%")
                  ->orWhereHas('borrower', fn ($bq) =>
                      $bq->where('first_name', 'like', "%$search%")
                         ->orWhere('last_name', 'like', "%$search%")
                         ->orWhere('nrc_number', 'like', "%$search%")
                         ->orWhere('phone_primary', 'like', "%$search%")
                  );
            });
        }

        if ($request->officer_id) {
            $query->where('applied_by', $request->officer_id);
        }

        // Filter by severity bucket using days overdue
        if ($severity = $request->severity) {
            $ranges = [
                '1_7'   => [1, 7],
                '8_30'  => [8, 30],
                '31_60' => [31, 60],
                '61_90' => [61, 90],
                '90+'   => [91, 9999],
            ];
            if (isset($ranges[$severity])) {
                [$min, $max] = $ranges[$severity];
                $query->whereBetween(DB::raw('DATEDIFF(NOW(), maturity_date)'), [$min, $max]);
            }
        }

        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $sortBy  = $request->sort_by === 'total_outstanding'
            ? 'loan_balances.total_outstanding'
            : 'days_overdue';

        // days_overdue = days since maturity_date (loan term ended, borrower hasn't settled)
        $query->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
              ->select('loans.*', DB::raw('DATEDIFF(NOW(), loans.maturity_date) as days_overdue'))
              ->orderBy($sortBy, $sortDir);

        return response()->json($query->paginate($request->per_page ?? 25));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/overdue/loans/{loan}
    // Detailed view: schedule, penalty history, activity log
    // ──────────────────────────────────────────────────────────────────────────
    public function loanDetail(Loan $loan): JsonResponse
    {
        abort_unless(in_array($loan->status, ['overdue', 'active']), 404);

        $loan->load([
            'borrower',
            'loanProduct',
            'loanBalance',
            'loanSchedule',
            'penalties.loanSchedule',
            'penalties.waivedBy:id,name',
            'appliedBy:id,name',
        ]);

        $daysOverdue = (int) DB::table('loan_schedule')
            ->selectRaw('DATEDIFF(NOW(), MIN(due_date)) as d')
            ->where('loan_id', $loan->id)
            ->where('status', 'overdue')
            ->value('d');

        $overdueInstalments = DB::table('loan_schedule')
            ->where('loan_id', $loan->id)
            ->where('status', 'overdue')
            ->count();

        $data                         = $loan->toArray();
        $data['days_overdue']         = $daysOverdue;
        $data['overdue_instalments_count'] = $overdueInstalments;

        return response()->json($data);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/overdue/collections-queue
    // Priority-sorted queue for the collections workflow view
    // ──────────────────────────────────────────────────────────────────────────
    public function collectionsQueue(Request $request): JsonResponse
    {
        $daysSubq = DB::table('loan_schedule')
            ->selectRaw('loan_id, DATEDIFF(NOW(), MIN(due_date)) as days_overdue')
            ->where('status', 'overdue')
            ->groupBy('loan_id');

        $loans = Loan::overdue()
            ->with([
                'borrower:id,first_name,last_name,phone_primary',
                'loanBalance',
                'appliedBy:id,name',
            ])
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->leftJoinSub($daysSubq, 'overdue_days', 'loans.id', '=', 'overdue_days.loan_id')
            ->select('loans.*', DB::raw('COALESCE(overdue_days.days_overdue, 0) as days_overdue'), 'loan_balances.total_outstanding')
            ->orderByDesc('loan_balances.total_outstanding')
            ->limit(50)
            ->get();

        // Assign P1/P2/P3 priority based on days overdue and amount
        $queue = $loans->map(fn ($loan) => [
            'loan'          => $loan,
            'priority'      => $this->calcPriority($loan),
            'attempt_count' => 0,
            'last_note'     => null,
            'needs_visit'   => ($loan->days_overdue ?? 0) > 30,
        ]);

        return response()->json($queue->sortBy('priority')->values());
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/overdue/log-contact
    // Body: { loan_id, method, outcome, notes?, promise_to_pay_date? }
    // ──────────────────────────────────────────────────────────────────────────
    public function logContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_id'             => 'required|exists:loans,id',
            'method'              => 'required|in:phone_call,sms,field_visit,email',
            'outcome'             => 'required|in:connected_committed,connected_no_commitment,no_answer,not_reachable,wrong_number,visited_in',
            'notes'               => 'nullable|string|max:1000',
            'promise_to_pay_date' => 'nullable|date|after:today',
        ]);

        $loan = Loan::findOrFail($validated['loan_id']);

        // Store as a reminder record with type = contact_log
        $log = $loan->reminders()->create([
            'reminder_type'   => 'contact_log',
            'contact_method'  => $validated['method'],
            'outcome'         => $validated['outcome'],
            'notes'           => $validated['notes'],
            'promise_date'    => $validated['promise_to_pay_date'],
            'sent_by'         => Auth::id(),
            'sent_at'         => now(),
            'status'          => 'sent',
        ]);

        return response()->json([
            'message' => 'Contact attempt logged.',
            'log'     => $log,
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/overdue/escalate/{loan}
    // Body: { type, assigned_to, notes?, notify_borrower? }
    // ──────────────────────────────────────────────────────────────────────────
    public function escalate(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'type'             => 'required|in:legal_notice,external_collector,collateral_repossession,court_proceedings',
            'assigned_to'      => 'required|in:legal_team,debt_recovery_firm,legal_officer',
            'notes'            => 'nullable|string|max:1000',
            'notify_borrower'  => 'boolean',
        ]);

        // Update loan status to 'defaulted' (escalated loans)
        $loan->update(['status' => 'defaulted']);

        // Log status history
        $loan->statusHistory()->create([
            'from_status' => 'overdue',
            'to_status'   => 'defaulted',
            'notes'       => "Escalated — {$validated['type']}. Assigned to: {$validated['assigned_to']}. " . ($validated['notes'] ?? ''),
            'changed_by'  => Auth::id(),
        ]);

        // Send SMS if requested
        if ($request->boolean('notify_borrower')) {
            // Dispatched via queue — ReminderService handles sending
            dispatch(new \App\Jobs\SendEscalationNotice($loan));
        }

        return response()->json([
            'message'    => 'Loan escalated to ' . str_replace('_', ' ', $validated['assigned_to']) . '.',
            'loan_status'=> 'defaulted',
        ]);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function calcPriority(Loan $loan): string
    {
        $days = $loan->days_overdue ?? 0;
        $amt  = $loan->total_outstanding ?? 0;
        if ($days >= 60 || $amt >= 20000) return 'P1';
        if ($days >= 30 || $amt >= 10000) return 'P2';
        return 'P3';
    }
}
