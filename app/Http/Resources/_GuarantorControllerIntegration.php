<?php

namespace App\Http\Resources;

class _GuarantorControllerIntegration
{
    // index
    public function index($loan): JsonResponse
    {
        // BEFORE: return response()->json($loan->guarantors()->with(...)->get());
        return \App\Http\Resources\GuarantorResource::collection(
            $loan->guarantors()->with('borrower:id,first_name,last_name,phone_primary,nrc_number,kyc_status')->get()
        )->response();
    }

    // store
    public function store($request, $loan): JsonResponse
    {
        $guarantor = $loan->guarantors()->create([...$validated, 'added_by' => auth()->id()]);

        return response()->json([
            'message'   => 'Guarantor added.',
            'guarantor' => \App\Http\Resources\GuarantorResource::make($guarantor),
        ], 201);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// OverdueController — log-contact
// ─────────────────────────────────────────────────────────────────────────────
