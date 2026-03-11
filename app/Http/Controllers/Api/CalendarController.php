<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/calendar/schedule
    // Query params: from (YYYY-MM-DD), to (YYYY-MM-DD)
    // Returns loan_schedule rows for the window + all currently-overdue rows
    // ──────────────────────────────────────────────────────────────────────────
    public function schedule(Request $request): JsonResponse
    {
        $from = $request->from ?? now()->subDays(30)->toDateString();
        $to   = $request->to   ?? now()->addDays(60)->toDateString();

        $rows = DB::table('loan_schedule as ls')
            ->join('loans', 'ls.loan_id', '=', 'loans.id')
            ->join('borrowers as b', 'loans.borrower_id', '=', 'b.id')
            ->leftJoin('loan_products as lp', 'loans.loan_product_id', '=', 'lp.id')
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('ls.due_date', [$from, $to])
                  ->orWhere('ls.status', 'overdue');
            })
            ->whereNotIn('loans.status', ['pending', 'pending_approval', 'rejected', 'cancelled'])
            ->select(
                'ls.id',
                'ls.loan_id',
                'ls.instalment_number',
                'ls.due_date',
                'ls.total_due',
                'ls.status',
                'ls.paid_at',
                'loans.loan_number',
                'loans.term_months',
                'loans.monthly_instalment',
                DB::raw("CONCAT(b.first_name, ' ', b.last_name) as borrower_name"),
                'b.borrower_number',
                'lp.name as product_name',
                DB::raw('DATEDIFF(NOW(), ls.due_date) as days_overdue_raw'),
            )
            ->orderBy('ls.due_date')
            ->get();

        // Outstanding penalty totals per schedule row
        $penaltyByRow = DB::table('penalties')
            ->whereIn('loan_schedule_id', $rows->pluck('id'))
            ->where('status', 'outstanding')
            ->groupBy('loan_schedule_id')
            ->select('loan_schedule_id', DB::raw('SUM(penalty_amount) as total'))
            ->pluck('total', 'loan_schedule_id');

        $today = now()->toDateString();

        $mapped = $rows->map(function ($r) use ($penaltyByRow, $today) {
            $status = $r->status;
            // Pending rows with due_date == today become 'due'
            if ($status === 'pending' && $r->due_date === $today) {
                $status = 'due';
            }
            $daysOverdue = ($r->due_date < $today && $status === 'overdue')
                ? max(0, (int) $r->days_overdue_raw)
                : 0;

            return [
                'id'         => $r->id,
                'loan_id'    => $r->loan_id,
                'loan'       => $r->loan_number,
                'bnum'       => $r->borrower_number,
                'name'       => trim($r->borrower_name),
                'product'    => $r->product_name ?? '—',
                'inst'       => $r->instalment_number,
                'totalInst'  => $r->term_months,
                'date'       => $r->due_date,
                'amount'     => (float) $r->total_due,
                'status'     => $status,
                'daysOverdue'=> $daysOverdue,
                'penalty'    => (float) ($penaltyByRow[$r->id] ?? 0),
                'paidSoFar'  => $status === 'paid' ? (float) $r->total_due : 0,
            ];
        });

        return response()->json($mapped);
    }
}
