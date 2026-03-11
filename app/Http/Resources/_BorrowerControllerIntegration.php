<?php

namespace App\Http\Resources;

class _BorrowerControllerIntegration
{
    // index — paginated list with collection wrapper
    public function index($request): JsonResponse
    {
        // ... query ...
        $borrowers = $query->paginate($request->per_page ?? 20);

        // BEFORE: return response()->json($borrowers);
        return (new BorrowerCollection($borrowers))->response();
    }

    // store — single resource after creation
    public function store($request): JsonResponse
    {
        $borrower = $this->borrowerService->create($validated, auth()->id());

        // BEFORE: return response()->json(['message' => '...', 'borrower' => $borrower->load(...)], 201);
        return response()->json([
            'message'  => 'Borrower registered successfully.',
            'borrower' => BorrowerResource::make(
                $borrower->load(['assignedOfficer:id,name'])
            ),
        ], 201);
    }

    // show — full detail resource
    public function show($borrower): JsonResponse
    {
        $borrower->load([
            'assignedOfficer:id,name',
            'loans.loanProduct:id,name',
            'loans.loanBalance',
            'documents',
            'guarantors',
        ])->loadCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()]);

        // BEFORE: return response()->json($borrower);
        return BorrowerResource::make($borrower)->response();
    }

    // update — single resource after update
    public function update($request, $borrower): JsonResponse
    {
        $borrower = $this->borrowerService->update($borrower, $validated);

        return response()->json([
            'message'  => 'Borrower updated successfully.',
            'borrower' => BorrowerResource::make($borrower->load('assignedOfficer:id,name')),
        ]);
    }

    // statement — structured response (no resource wrapper needed here)
    // Keep as plain JSON — it's already a bespoke report shape.
}


// ─────────────────────────────────────────────────────────────────────────────
// LoanController
// ─────────────────────────────────────────────────────────────────────────────
