<?php

namespace App\Http\Resources;

class _PenaltyControllerIntegration
{
    // index
    public function index($request): JsonResponse
    {
        $penalties = $query->paginate($request->per_page ?? 25);

        return \App\Http\Resources\PenaltyResource::collection($penalties)->response();
    }

    // show
    public function show($penalty): JsonResponse
    {
        $penalty->load(['loan.borrower:id,first_name,last_name,borrower_number', 'loanSchedule', 'waivedBy:id,name']);

        return \App\Http\Resources\PenaltyResource::make($penalty)->response();
    }

    // applyDaily, waive, bulkWaive — keep as plain JSON summary responses
    // (they don't return individual penalty models — they return operation summaries)
}


// ─────────────────────────────────────────────────────────────────────────────
// CollateralController
// ─────────────────────────────────────────────────────────────────────────────
