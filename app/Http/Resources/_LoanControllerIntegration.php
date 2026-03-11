<?php

namespace App\Http\Resources;

class _LoanControllerIntegration
{
    // index — paginated collection with portfolio aggregate injected
    public function index($request): JsonResponse
    {
        $loans = $query->paginate($request->per_page ?? 20);

        // Compute aggregate totals for the current filter set (unsliced)
        $allIds       = $query->pluck('loans.id');
        $aggregate    = \App\Models\Loan::whereIn('id', $allIds)
            ->join('loan_balances', 'loans.id', '=', 'loan_balances.loan_id')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN loans.status = "active"  THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN loans.status = "overdue" THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN loans.status = "pending_approval" THEN 1 ELSE 0 END) as pending,
                SUM(loan_balances.total_outstanding) as total_outstanding
            ')
            ->first();

        // BEFORE: return response()->json($query->paginate(...));
        return (new LoanCollection($loans))
            ->additional(['aggregate' => $aggregate])
            ->response();
    }

    // show — full detail
    public function show($loan): JsonResponse
    {
        $loan->load([
            'borrower', 'loanProduct', 'collateralAsset',
            'loanBalance', 'loanSchedule', 'payments.paymentAllocations',
            'penalties', 'guarantors.borrower', 'statusHistory.changedBy:id,name',
            'appliedBy:id,name', 'approvedBy:id,name', 'disbursedBy:id,name',
        ]);

        // BEFORE: return response()->json($loan);
        return LoanResource::make($loan)->response();
    }

    // store / approve / disburse / earlySettle / writeOff — single resource
    public function store($request): JsonResponse
    {
        $loan = $this->loanService->apply($validated, auth()->id());

        return response()->json([
            'message' => 'Loan application submitted successfully.',
            'loan'    => LoanResource::make($loan->load(['borrower', 'loanProduct', 'loanSchedule'])),
        ], 201);
    }

    // approve
    public function approve($request, $loan): JsonResponse
    {
        $loan = $this->loanService->approve($loan, auth()->id(), $request->approval_notes);

        return response()->json([
            'message' => 'Loan approved.',
            'loan'    => LoanResource::make($loan->fresh(['statusHistory'])),
        ]);
    }

    // disburse
    public function disburse($request, $loan): JsonResponse
    {
        $loan = $this->loanService->disburse($loan, auth()->id(), $validated);

        return response()->json([
            'message' => 'Loan disbursed successfully. Schedule generated.',
            'loan'    => LoanResource::make($loan->load(['loanBalance', 'loanSchedule'])),
        ]);
    }

    // schedule — plain array (already well-structured, no resource needed)
    // calculate — plain array (calculator preview — no model to wrap)
    // activity — plain array (interleaved timeline)
}


// ─────────────────────────────────────────────────────────────────────────────
// PaymentController
// ─────────────────────────────────────────────────────────────────────────────
