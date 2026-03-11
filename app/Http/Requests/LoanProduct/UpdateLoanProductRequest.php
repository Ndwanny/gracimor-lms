<?php

namespace App\Http\Requests\LoanProduct;

class UpdateLoanProductRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo');
    }

    public function prepareForValidation(): void
    {
        if ($this->code) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('loanProduct')?->id;

        return [
            'name'                    => ['sometimes', 'required', 'string', 'min:3', 'max:100',
                                          "unique:loan_products,name,{$productId}"],
            'code'                    => ['sometimes', 'required', 'string', 'max:20', 'alpha_dash',
                                          "unique:loan_products,code,{$productId}"],
            'collateral_type'         => ['sometimes', 'required', 'in:vehicle,land,both'],
            'description'             => ['nullable', 'string', 'max:1000'],
            'is_active'               => ['boolean'],

            'interest_rate'           => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:100'],
            'min_interest_rate'       => ['nullable', 'numeric', 'min:0.01'],
            'max_interest_rate'       => ['nullable', 'numeric', 'max:100'],
            'interest_method'         => ['sometimes', 'required', 'in:reducing_balance,flat_rate'],

            'min_term_months'         => ['sometimes', 'required', 'integer', 'min:1', 'max:120'],
            'max_term_months'         => ['sometimes', 'required', 'integer', 'min:1', 'max:120'],
            'min_loan_amount'         => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_loan_amount'         => ['sometimes', 'required', 'numeric'],
            'max_ltv_percent'         => ['sometimes', 'required', 'numeric', 'min:1', 'max:100'],

            'processing_fee_flat'     => ['nullable', 'numeric', 'min:0'],
            'processing_fee_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],

            'penalty_rate_percent'    => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'penalty_basis'           => ['sometimes', 'required', 'in:instalment,outstanding_balance'],
            'grace_period_days'       => ['sometimes', 'required', 'integer', 'min:0', 'max:90'],

            'allow_early_settlement'  => ['boolean'],
            'early_settlement_method' => ['nullable', 'in:prorated,rebate_78,none'],
            'allow_rate_override'     => ['boolean'],
            'require_guarantor'       => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $product = $this->route('loanProduct');

            if (!$product) {
                return;
            }

            // Warn if deactivating a product with active loans
            if ($this->has('is_active') && !$this->boolean('is_active')) {
                $activeCount = $product->loans()->active()->count();
                if ($activeCount > 0) {
                    $v->errors()->add('is_active',
                        "This product has {$activeCount} active loan(s). Deactivating it will prevent new " .
                        "applications but will not affect existing loans."
                    );
                }
            }

            // Prevent reducing max_term below existing loans' longest term
            if ($this->max_term_months) {
                $maxExisting = $product->loans()
                    ->whereIn('status', ['active', 'overdue'])
                    ->max('term_months');

                if ($maxExisting && $this->max_term_months < $maxExisting) {
                    $v->errors()->add('max_term_months',
                        "Cannot reduce maximum term below {$maxExisting} months — existing active " .
                        "loans use this term length."
                    );
                }
            }

            // Cross-validate rate bounds if both are being updated
            $minRate = $this->min_interest_rate ?? $product->min_interest_rate;
            $maxRate = $this->max_interest_rate ?? $product->max_interest_rate;

            if ($minRate && $maxRate && $minRate >= $maxRate) {
                $v->errors()->add('max_interest_rate',
                    'Maximum interest rate must be greater than the minimum rate.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.unique'         => 'Another product with this name already exists.',
            'code.unique'         => 'Another product with this code already exists.',
            'code.alpha_dash'     => 'Product code may only contain letters, numbers, hyphens, and underscores.',
            'interest_method.in'  => 'Method must be "reducing_balance" or "flat_rate".',
            'penalty_basis.in'    => 'Penalty basis must be "instalment" or "outstanding_balance".',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// StoreUserRequest
// POST /api/users
// ═══════════════════════════════════════════════════════════════════════════════
