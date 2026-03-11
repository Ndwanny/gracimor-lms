<?php

namespace App\Http\Resources;

class _CollateralControllerIntegration
{
    // index
    public function index($request): JsonResponse
    {
        $assets = $query->paginate($request->per_page ?? 20);

        return \App\Http\Resources\CollateralAssetResource::collection($assets)->response();
    }

    // show
    public function show($collateralAsset): JsonResponse
    {
        $collateralAsset->load(['loans.borrower:id,first_name,last_name', 'registeredBy:id,name']);

        return \App\Http\Resources\CollateralAssetResource::make($collateralAsset)->response();
    }

    // store / update — wrap in resource with message envelope
    public function store($request): JsonResponse
    {
        $asset = $this->collateralService->register($validated, auth()->id());

        return response()->json([
            'message' => 'Collateral registered.',
            'asset'   => \App\Http\Resources\CollateralAssetResource::make($asset),
        ], 201);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// LoanProductController
// ─────────────────────────────────────────────────────────────────────────────
