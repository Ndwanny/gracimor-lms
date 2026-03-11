<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Penalty;
use App\Services\PenaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenaltyController extends Controller
{
    public function __construct(protected PenaltyService $penaltyService) {}

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/penalties
    // Query params: loan_id, borrower_id, status, date_from, date_to, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Penalty::with([
            'loan:id,loan_number,borrower_id',
            'loan.borrower:id,first_name,last_name',
            'scheduleRow:id,instalment_number,due_date',
            'waivedBy:id,name',
        ])->latest('applied_date');

        if ($request->loan_id) {
            $query->where('loan_id', $request->loan_id);
        }

        if ($request->borrower_id) {
            $query->whereHas('loan', fn ($q) => $q->where('borrower_id', $request->borrower_id));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('applied_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('applied_date', '<=', $request->date_to);
        }

        return response()->json($query->paginate($request->per_page ?? 25));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/penalties/apply-daily
    // Manually trigger the daily penalty job for a given date
    // Body: { as_of_date, update_statuses? }
    // Called by the settings UI "Run Penalty Job" button, or by the scheduler
    // ──────────────────────────────────────────────────────────────────────────
    public function applyDaily(Request $request): JsonResponse
    {
        $request->validate([
            'as_of_date'      => 'required|date|before_or_equal:today',
            'update_statuses' => 'boolean',
        ]);

        $result = $this->penaltyService->applyDailyPenalties(
            $request->as_of_date,
            $request->boolean('update_statuses', true),
        );

        return response()->json([
            'message'              => 'Penalty job completed.',
            'instalments_affected' => $result['instalments_affected'],
            'total_penalties'      => $result['total_penalties'],
            'total_amount'         => $result['total_amount'],
            'loans_updated'        => $result['loans_updated'],
            'as_of_date'           => $request->as_of_date,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/penalties/waive
    // Waive penalties for one loan — all outstanding or a specific instalment
    // Body: { loan_id, scope, loan_schedule_id?, reason, notes? }
    // ──────────────────────────────────────────────────────────────────────────
    public function waive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_id'           => 'required|exists:loans,id',
            'scope'             => 'required|in:all,instalment',
            'loan_schedule_id'  => 'required_if:scope,instalment|nullable|exists:loan_schedule,id',
            'reason'            => 'required|in:hardship,error,goodwill,health,death,management_decision,other',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $result = $this->penaltyService->waive(
            $validated['loan_id'],
            $validated['scope'],
            $validated['loan_schedule_id'] ?? null,
            $validated['reason'],
            $validated['notes'] ?? null,
            Auth::id(),
        );

        return response()->json([
            'message'         => 'Penalties waived successfully.',
            'penalties_waived'=> $result['count'],
            'amount_waived'   => $result['amount'],
            'remaining'       => $result['remaining'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/penalties/bulk-waive
    // Waive all outstanding penalties across multiple loans
    // Body: { loan_ids[], reason, notes?, auth_code }
    // ──────────────────────────────────────────────────────────────────────────
    public function bulkWaive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'loan_ids'   => 'required|array|min:1',
            'loan_ids.*' => 'exists:loans,id',
            'reason'     => 'required|in:hardship,error,goodwill,health,death,management_decision,other',
            'notes'      => 'nullable|string|max:1000',
            'auth_code'  => 'required|string',
        ]);

        // Validate authorisation code (should match current user's 2FA or manager PIN)
        if (!$this->penaltyService->validateAuthCode($validated['auth_code'], Auth::user())) {
            return response()->json(['message' => 'Invalid authorisation code.'], 403);
        }

        $result = $this->penaltyService->bulkWaive(
            $validated['loan_ids'],
            $validated['reason'],
            $validated['notes'] ?? null,
            Auth::id(),
        );

        return response()->json([
            'message'         => "Bulk waiver completed — {$result['loans_affected']} loans.",
            'loans_affected'  => $result['loans_affected'],
            'penalties_waived'=> $result['count'],
            'total_waived'    => $result['amount'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/penalties/{penalty}
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Penalty $penalty): JsonResponse
    {
        $penalty->load([
            'loan.borrower:id,first_name,last_name,borrower_number',
            'scheduleRow',
            'waivedBy:id,name',
        ]);

        return response()->json($penalty);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/penalties/accrual-preview
    // Preview how much penalty would be applied if run today
    // ──────────────────────────────────────────────────────────────────────────
    public function accrualPreview(): JsonResponse
    {
        $preview = $this->penaltyService->accrualPreview();

        return response()->json([
            'as_of'                  => today()->toDateString(),
            'overdue_instalments'    => $preview['overdue_instalments'],
            'total_daily_accrual'    => $preview['total_daily_accrual'],
            'per_loan'               => $preview['per_loan'],
        ]);
    }
}
