<?php

namespace App\Http\Resources;

class _PaymentControllerIntegration
{
    // index
    public function index($request): JsonResponse
    {
        $payments = $query->paginate($request->per_page ?? 25);

        // BEFORE: return response()->json($query->paginate(...));
        return \App\Http\Resources\PaymentResource::collection($payments)->response();
    }

    // store
    public function store($request): JsonResponse
    {
        $result = $this->paymentService->record($loan, $validated, auth()->id());

        return response()->json([
            'message'     => 'Payment recorded successfully.',
            'payment'     => \App\Http\Resources\PaymentResource::make(
                $result['payment']->load('paymentAllocations')
            ),
            'loan_status' => $result['loan_status'],
            'receipt'     => $result['receipt_number'],
        ], 201);
    }

    // show
    public function show($payment): JsonResponse
    {
        $payment->load(['loan.borrower', 'loan.loanProduct:id,name', 'paymentAllocations', 'recordedBy:id,name']);

        // BEFORE: return response()->json($payment);
        return \App\Http\Resources\PaymentResource::make($payment)->response();
    }

    // receipt — keep as plain JSON (it's a print-ready report shape, not a model resource)
}


// ─────────────────────────────────────────────────────────────────────────────
// PenaltyController
// ─────────────────────────────────────────────────────────────────────────────
