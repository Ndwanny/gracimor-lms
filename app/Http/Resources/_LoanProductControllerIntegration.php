<?php

namespace App\Http\Resources;

class _LoanProductControllerIntegration
{
    // index — returns a plain collection (no pagination on products — usually < 10)
    public function index($request): JsonResponse
    {
        $products = $query->withCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get();

        return \App\Http\Resources\LoanProductResource::collection($products)->response();
    }

    // show
    public function show($loanProduct): JsonResponse
    {
        $loanProduct->loadCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()])
                    ->loadSum(['loans as total_disbursed' => fn ($q) => $q->whereNotNull('disbursed_at')], 'principal_amount');

        return \App\Http\Resources\LoanProductResource::make($loanProduct)->response();
    }

    // store / update — wrap in resource with message
    public function store($request): JsonResponse
    {
        $product = \App\Models\LoanProduct::create($validated);

        return response()->json([
            'message' => 'Product created.',
            'product' => \App\Http\Resources\LoanProductResource::make($product),
        ], 201);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// UserController
// ─────────────────────────────────────────────────────────────────────────────
