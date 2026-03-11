<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanProductController extends Controller
{
    // GET /api/loan-products
    public function index(Request $request): JsonResponse
    {
        $query = LoanProduct::withCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()]);

        if ($request->active_only) {
            $query->where('is_active', true);
        }

        return response()->json($query->orderBy('name')->get());
    }

    // POST /api/loan-products
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:100|unique:loan_products,name',
            'code'                    => 'required|string|max:20|unique:loan_products,code',
            'collateral_type'         => 'required|in:vehicle,land,both,none',
            'default_interest_rate'   => 'required|numeric|min:0.01|max:100',
            'min_interest_rate'       => 'nullable|numeric|min:0.01',
            'max_interest_rate'       => 'nullable|numeric|max:100',
            'interest_method'         => 'required|in:reducing_balance,flat_rate',
            'min_term_months'         => 'required|integer|min:1',
            'max_term_months'         => 'required|integer|min:1',
            'min_loan_amount'         => 'required|numeric|min:0',
            'max_loan_amount'         => 'required|numeric',
            'max_ltv_percent'         => 'required|numeric|min:1|max:100',
            'processing_fee_fixed'    => 'nullable|numeric|min:0',
            'processing_fee_percent'  => 'nullable|numeric|min:0|max:100',
            'penalty_rate_percent'    => 'required|numeric|min:0|max:100',
            'grace_period_days'       => 'required|integer|min:0',
            'allow_early_settlement'  => 'boolean',
            'early_settlement_method' => 'nullable|in:prorated,rebate_78,none',
            'description'             => 'nullable|string',
            'is_active'               => 'boolean',
        ]);

        $product = LoanProduct::create($validated);

        return response()->json(['message' => 'Product created.', 'product' => $product], 201);
    }

    // GET /api/loan-products/{product}
    public function show(LoanProduct $loanProduct): JsonResponse
    {
        $loanProduct->loadCount(['loans', 'loans as active_loans_count' => fn ($q) => $q->active()])
                    ->loadSum(['loans as total_disbursed' => fn ($q) => $q->whereNotNull('disbursed_at')], 'principal_amount');

        return response()->json($loanProduct);
    }

    // PUT /api/loan-products/{product}
    public function update(Request $request, LoanProduct $loanProduct): JsonResponse
    {
        $validated = $request->validate([
            'name'                    => "sometimes|required|string|max:100|unique:loan_products,name,{$loanProduct->id}",
            'code'                    => "sometimes|required|string|max:20|unique:loan_products,code,{$loanProduct->id}",
            'collateral_type'         => 'sometimes|required|in:vehicle,land,both,none',
            'default_interest_rate'   => 'sometimes|required|numeric|min:0.01|max:100',
            'min_interest_rate'       => 'nullable|numeric|min:0.01',
            'max_interest_rate'       => 'nullable|numeric|max:100',
            'interest_method'         => 'sometimes|required|in:reducing_balance,flat_rate',
            'min_term_months'         => 'sometimes|required|integer|min:1',
            'max_term_months'         => 'sometimes|required|integer|min:1',
            'min_loan_amount'         => 'sometimes|required|numeric|min:0',
            'max_loan_amount'         => 'sometimes|required|numeric',
            'max_ltv_percent'         => 'sometimes|required|numeric|min:1|max:100',
            'processing_fee_fixed'    => 'nullable|numeric|min:0',
            'processing_fee_percent'  => 'nullable|numeric|min:0|max:100',
            'penalty_rate_percent'    => 'sometimes|required|numeric|min:0|max:100',
            'grace_period_days'       => 'sometimes|required|integer|min:0',
            'allow_early_settlement'  => 'boolean',
            'early_settlement_method' => 'nullable|in:prorated,rebate_78,none',
            'description'             => 'nullable|string',
            'is_active'               => 'boolean',
        ]);

        $loanProduct->update($validated);

        return response()->json(['message' => 'Product updated.', 'product' => $loanProduct->fresh()]);
    }

    // DELETE /api/loan-products/{product}
    public function destroy(LoanProduct $loanProduct): JsonResponse
    {
        if ($loanProduct->loans()->active()->exists()) {
            return response()->json(['message' => 'Cannot delete a product with active loans. Deactivate it instead.'], 422);
        }
        $loanProduct->delete();
        return response()->json(['message' => 'Product deleted.']);
    }
}
