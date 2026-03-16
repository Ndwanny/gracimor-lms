<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Services\LoanService;
use App\Services\LoanCalculatorService;
use App\Services\PaymentService;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService           $loanService,
        protected LoanCalculatorService $calculator,
        protected PaymentService        $paymentService,
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/loans
    // Query params: search, status, borrower_id, loan_product_id, officer_id,
    //               date_from, date_to, sort_by, sort_dir, per_page
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Loan::with([
            'borrower:id,borrower_number,first_name,last_name,phone_primary',
            'loanProduct:id,name,code',
            'collateralAsset:id,asset_type',
            'loanBalance',
            'appliedBy:id,name',
        ])->latest();

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

        if ($request->status) {
            $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->borrower_id) {
            $query->where('borrower_id', $request->borrower_id);
        }

        if ($request->loan_product_id) {
            $query->where('loan_product_id', $request->loan_product_id);
        }

        if ($request->officer_id) {
            $query->where('applied_by', $request->officer_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $allowedSorts = ['created_at', 'loan_number', 'principal_amount', 'disbursed_at', 'maturity_date'];
        $sortBy  = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans
    // Submit a new loan application
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'borrower_id'          => 'required|exists:borrowers,id',
            'loan_product_id'      => 'nullable|exists:loan_products,id',
            'collateral_asset_id'  => 'required|exists:collateral_assets,id',
            'principal_amount'     => 'required|numeric|min:1000',
            'term_months'          => 'required|integer|in:1,2,3,4',
            'first_repayment_date' => 'required|date|after_or_equal:today',
            'disbursement_method'  => 'required|in:cash,bank_transfer,mobile_money',
            'loan_purpose'         => 'nullable|string|max:500',
        ]);

        $loan = $this->loanService->apply($validated, Auth::user());

        return response()->json([
            'message' => 'Loan application submitted successfully.',
            'loan'    => $loan->load(['borrower', 'loanProduct', 'loanSchedule']),
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/loans/{loan}
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Loan $loan): JsonResponse
    {
        $loan->load([
            'borrower',
            'loanProduct',
            'collateralAsset',
            'loanBalance',
            'loanSchedule',
            'payments.paymentAllocations',
            'penalties',
            'guarantors.borrower',
            'statusHistory.changedBy:id,name',
            'appliedBy:id,name',
            'approvedBy:id,name',
            'disbursedBy:id,name',
        ]);

        return response()->json($loan);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/approve
    // Body: { approval_notes? }
    // ──────────────────────────────────────────────────────────────────────────
    public function approve(Request $request, Loan $loan): JsonResponse
    {
        if (!in_array($loan->status, ['pending', 'pending_approval'])) {
            return response()->json(['message' => "Loan is not pending approval (current status: {$loan->status})."], 422);
        }

        $request->validate(['approval_notes' => 'nullable|string|max:1000']);

        $loan = $this->loanService->approve($loan, Auth::user(), null, $request->approval_notes ?? '');

        return response()->json([
            'message' => 'Loan approved.',
            'loan'    => $loan->fresh(['statusHistory']),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/reject
    // Body: { rejection_reason }
    // ──────────────────────────────────────────────────────────────────────────
    public function reject(Request $request, Loan $loan): JsonResponse
    {
        if (!in_array($loan->status, ['pending', 'pending_approval', 'pending_documents'])) {
            return response()->json(['message' => 'Loan cannot be rejected at this stage.'], 422);
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $loan = $this->loanService->reject($loan, Auth::user(), $request->rejection_reason);

        return response()->json([
            'message' => 'Loan rejected.',
            'loan'    => $loan->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/disburse
    // Body: { disbursement_method, disbursement_reference?, disburse_notes? }
    // ──────────────────────────────────────────────────────────────────────────
    public function disburse(Request $request, Loan $loan): JsonResponse
    {
        if ($loan->status !== 'approved') {
            return response()->json(['message' => "Loan must be approved before disbursement (current: {$loan->status})."], 422);
        }

        $validated = $request->validate([
            'disbursement_method'    => 'required|in:cash,bank_transfer,mobile_money',
            'disbursement_reference' => 'nullable|string|max:100',
            'disburse_notes'         => 'nullable|string|max:500',
        ]);

        $loan = $this->loanService->disburse($loan, Auth::user(), $validated);

        return response()->json([
            'message' => 'Loan disbursed successfully. Schedule generated.',
            'loan'    => $loan->load(['loanBalance', 'loanSchedule']),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/early-settle
    // Body: { payment_method, payment_date?, reference?, notes? }
    // The settlement amount is calculated server-side using schedule due dates.
    // ──────────────────────────────────────────────────────────────────────────
    public function earlySettle(Request $request, Loan $loan): JsonResponse
    {
        if (!in_array($loan->status, ['active', 'overdue'])) {
            return response()->json(['message' => 'Only active or overdue loans can be early-settled.'], 422);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'payment_date'   => 'nullable|date',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        $payment = $this->paymentService->earlySettle($loan, Auth::user(), [
            'payment_method'    => $validated['payment_method'],
            'payment_date'      => $validated['payment_date'] ?? today()->toDateString(),
            'payment_reference' => $validated['reference'] ?? null,
            'notes'             => $validated['notes'] ?? null,
        ]);

        $freshLoan = $loan->fresh();

        return response()->json([
            'message'           => 'Loan early-settled and closed.',
            'loan_status'       => $freshLoan->status,
            'receipt'           => $payment->receipt_number,
            'settlement_amount' => $freshLoan->early_settlement_amount,
            'discount'          => $freshLoan->early_settlement_discount,
            'payment'           => $payment->load('paymentAllocations'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/write-off
    // Body: { reason }
    // ──────────────────────────────────────────────────────────────────────────
    public function writeOff(Request $request, Loan $loan): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $loan = $this->loanService->writeOff($loan, Auth::user(), $request->reason);

        return response()->json([
            'message' => 'Loan written off.',
            'loan'    => $loan->fresh(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/loans/{loan}/schedule
    // Returns the full repayment schedule for a loan
    // ──────────────────────────────────────────────────────────────────────────
    public function schedule(Loan $loan): JsonResponse
    {
        $schedule = $loan->loanSchedule()
            ->orderBy('instalment_number')
            ->get();

        return response()->json([
            'loan_number'      => $loan->loan_number,
            'principal_amount' => $loan->principal_amount,
            'total_repayable'  => $loan->total_repayable,
            'total_interest'   => $loan->total_interest,
            'schedule'         => $schedule,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/loans/{loan}/activity
    // Returns status history + payments + penalties interleaved, sorted by date
    // ──────────────────────────────────────────────────────────────────────────
    public function activity(Loan $loan): JsonResponse
    {
        $statusHistory = $loan->statusHistory()
            ->with('changedBy:id,name')
            ->get()
            ->map(fn ($h) => [
                'type'       => 'status_change',
                'date'       => $h->created_at,
                'title'      => "Status → {$h->to_status}",
                'actor'      => $h->changedBy?->name,
                'notes'      => $h->notes,
            ]);

        $payments = $loan->payments()
            ->with('recordedBy:id,name')
            ->get()
            ->map(fn ($p) => [
                'type'       => 'payment',
                'date'       => $p->payment_date,
                'title'      => "Payment received — K " . number_format($p->amount_received, 2),
                'actor'      => $p->recordedBy?->name,
                'reference'  => $p->receipt_number,
                'notes'      => $p->notes,
            ]);

        $penalties = $loan->penalties()
            ->with('waivedBy:id,name')
            ->get()
            ->map(fn ($p) => [
                'type'       => $p->status === 'waived' ? 'penalty_waived' : 'penalty',
                'date'       => $p->applied_date,
                'title'      => $p->status === 'waived'
                    ? "Penalty waived — K " . number_format($p->penalty_amount, 2)
                    : "Penalty applied — K " . number_format($p->penalty_amount, 2),
                'actor'      => $p->waivedBy?->name,
                'notes'      => $p->waiver_reason,
            ]);

        $activity = $statusHistory->concat($payments)->concat($penalties)
            ->sortByDesc('date')
            ->values();

        return response()->json($activity);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/calculate
    // Preview: calculate schedule without creating a loan
    // Body: { principal, rate, term_months, method, first_repayment_date }
    // ──────────────────────────────────────────────────────────────────────────
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'principal'            => 'required|numeric|min:1',
            'rate'                 => 'required|numeric|min:0.01|max:100',
            'term_months'          => 'required|integer|min:1|max:60',
            'method'               => 'required|in:reducing_balance,flat_rate',
            'first_repayment_date' => 'required|date|after:today',
        ]);

        $result = $this->calculator->previewSchedule(
            $validated['principal'],
            $validated['rate'],
            $validated['term_months'],
            $validated['method'],
            $validated['first_repayment_date'],
        );

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/signatures
    // Body: { role: 'borrower'|'officer', signature: 'data:image/png;base64,...' }
    // Save the e-signature for a loan agreement
    // ──────────────────────────────────────────────────────────────────────────
    public function saveSignature(Request $request, Loan $loan): JsonResponse
    {
        $validated = $request->validate([
            'role'      => 'required|in:borrower,officer',
            'signature' => 'required|string|starts_with:data:image/',
        ]);

        $col   = $validated['role'] . '_signature';
        $atCol = $validated['role'] . '_signed_at';

        $loan->update([
            $col   => $validated['signature'],
            $atCol => now(),
        ]);

        return response()->json([
            'message'   => ucfirst($validated['role']) . ' signature saved.',
            'signed_at' => $loan->fresh()->$atCol,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/loans/{loan}/signatures/{role}
    // Clear a saved signature for this loan
    // ──────────────────────────────────────────────────────────────────────────
    public function clearSignature(Loan $loan, string $role): JsonResponse
    {
        abort_unless(in_array($role, ['borrower', 'officer']), 422, 'Invalid role.');

        $loan->update([
            $role . '_signature' => null,
            $role . '_signed_at' => null,
        ]);

        return response()->json(['message' => ucfirst($role) . ' signature cleared.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/loans/{loan}/send-reminder
    // Manually send an email (+ SMS if configured) reminder to the borrower
    // for their next due or most recent overdue instalment.
    // ──────────────────────────────────────────────────────────────────────────
    public function sendReminder(Loan $loan, ReminderService $reminderService): JsonResponse
    {
        abort_unless(
            in_array($loan->status, ['active', 'overdue']),
            422,
            'Reminders can only be sent for active or overdue loans.'
        );

        $loan->load([
            'borrower:id,first_name,last_name,phone_primary,email',
            'loanProduct:id,name,penalty_rate_percent',
            'loanBalance',
            'penalties',
            'appliedBy:id,name,phone',
        ]);

        // Find the most relevant instalment: first overdue, otherwise next pending
        $schedule = LoanSchedule::where('loan_id', $loan->id)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('due_date', 'asc')
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'No upcoming or overdue instalment found for this loan.'], 422);
        }

        $daysOverdue = now()->diffInDays(\Carbon\Carbon::parse($schedule->due_date), false);
        $isOverdue   = $daysOverdue < 0;

        $triggerKey = match (true) {
            $isOverdue && abs($daysOverdue) >= 30 => 'overdue_30_days',
            $isOverdue && abs($daysOverdue) >= 14 => 'overdue_14_days',
            $isOverdue && abs($daysOverdue) >= 7  => 'overdue_7_days',
            $isOverdue                             => 'overdue_1_day',
            $daysOverdue === 0                     => 'due_today',
            $daysOverdue <= 1                      => 'pre_due_1_day',
            $daysOverdue <= 3                      => 'pre_due_3_days',
            default                                => 'pre_due_7_days',
        };

        $channels = [];

        // Email — always attempt
        $emailResult = $reminderService->sendEmail($loan, $schedule, $triggerKey);
        if ($emailResult) {
            $channels[] = $emailResult;
        }

        // SMS — attempt for overdue loans if phone exists
        if ($isOverdue) {
            $smsResult = $reminderService->sendSms($loan, $schedule, $triggerKey);
            if ($smsResult) {
                $channels[] = $smsResult;
            }
        }

        if (empty($channels)) {
            return response()->json([
                'message' => 'No reminder sent — borrower has no email address or phone number on file.',
            ], 422);
        }

        $sent    = collect($channels)->where('status', 'sent')->count();
        $failed  = collect($channels)->where('status', 'failed')->count();
        $summary = collect($channels)->map(fn ($c) => strtoupper($c['channel']) . ': ' . $c['status'])->join(', ');

        return response()->json([
            'message'    => $sent > 0
                ? "Reminder sent via {$summary}."
                : "Reminder failed — {$summary}.",
            'trigger'    => $triggerKey,
            'channels'   => $channels,
            'sent'       => $sent,
            'failed'     => $failed,
        ], $sent > 0 ? 200 : 422);
    }
}
