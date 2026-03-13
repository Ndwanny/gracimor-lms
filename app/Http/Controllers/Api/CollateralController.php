<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Services\CollateralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollateralController extends Controller
{
    public function __construct(protected CollateralService $collateralService) {}

    // GET /api/collateral
    public function index(Request $request): JsonResponse
    {
        $query = CollateralAsset::with(['creator:id,name', 'borrower:id,first_name,last_name,borrower_number'])
            ->withCount('loans as loan_count');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_registration', 'like', "%$search%")
                  ->orWhere('vehicle_make',        'like', "%$search%")
                  ->orWhere('vehicle_model',       'like', "%$search%")
                  ->orWhere('plot_number',         'like', "%$search%")
                  ->orWhere('title_deed_number',   'like', "%$search%");
            });
        }

        if ($request->asset_type) {
            $query->where('asset_type', $request->asset_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->borrower_id) {
            $query->where('borrower_id', $request->borrower_id);
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    // POST /api/collateral
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'borrower_id'          => 'required|exists:borrowers,id',
            'asset_type'           => 'required|in:vehicle,land',
            // Vehicle fields
            'vehicle_registration' => 'required_if:asset_type,vehicle|nullable|string|max:20',
            'vehicle_make'         => 'nullable|string|max:100',
            'vehicle_model'        => 'nullable|string|max:100',
            'vehicle_year'         => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'vehicle_color'        => 'nullable|string|max:50',
            'engine_number'        => 'nullable|string|max:50',
            'chassis_vin'          => 'nullable|string|max:50',
            // Land fields
            'plot_number'          => 'required_if:asset_type,land|nullable|string|max:100',
            'title_deed_number'    => 'nullable|string|max:100',
            'land_address'         => 'nullable|string|max:500',
            'land_type'            => 'nullable|string|max:60',
            // Common
            'estimated_value'      => 'nullable|numeric|min:0',
            'valuation_date'       => 'nullable|date|before_or_equal:today',
            'valuation_firm'       => 'nullable|string|max:200',
        ]);

        $borrower = Borrower::findOrFail($validated['borrower_id']);
        $asset = $this->collateralService->register($borrower, $validated, Auth::user());

        return response()->json(['message' => 'Collateral registered.', 'asset' => $asset], 201);
    }

    // GET /api/collateral/{collateralAsset}
    public function show(CollateralAsset $collateralAsset): JsonResponse
    {
        $collateralAsset->load(['loans.borrower:id,first_name,last_name', 'creator:id,name', 'borrower:id,first_name,last_name,borrower_number']);
        return response()->json($collateralAsset);
    }

    // PUT /api/collateral/{collateralAsset}
    public function update(Request $request, CollateralAsset $collateralAsset): JsonResponse
    {
        $validated = $request->validate([
            'estimated_value'   => 'sometimes|required|numeric|min:1',
            'valuation_date'    => 'sometimes|required|date|before_or_equal:today',
            'valuation_firm'    => 'nullable|string|max:200',
            'status'            => 'sometimes|required|in:available,pledged,repossessed,released',
            'vehicle_color'     => 'nullable|string|max:50',
            'insurance_expiry'  => 'nullable|date',
            'insurance_company' => 'nullable|string|max:150',
        ]);

        $collateralAsset->update($validated);

        return response()->json(['message' => 'Collateral updated.', 'asset' => $collateralAsset->fresh()]);
    }

    // POST /api/collateral/{collateralAsset}/revalue
    public function revalue(Request $request, CollateralAsset $collateralAsset): JsonResponse
    {
        $validated = $request->validate([
            'estimated_value' => 'required|numeric|min:1',
            'valuation_date'  => 'required|date|before_or_equal:today',
            'valuation_firm'  => 'nullable|string|max:200',
            'notes'           => 'nullable|string|max:500',
        ]);

        $collateralAsset->update([
            'estimated_value' => $validated['estimated_value'],
            'valuation_date'  => $validated['valuation_date'],
            'valuation_firm'  => $validated['valuation_firm'] ?? $collateralAsset->valuation_firm,
        ]);

        // Recalculate LTV on active loan if attached
        if ($loan = $collateralAsset->activeLoan) {
            $newLtv = ($loan->principal_amount / $validated['estimated_value']) * 100;
            $loan->update(['ltv_at_origination' => round($newLtv, 2)]);
        }

        return response()->json([
            'message'     => 'Collateral revalued.',
            'new_value'   => $validated['estimated_value'],
            'updated_ltv' => isset($newLtv) ? round($newLtv, 2) : null,
        ]);
    }
}
