<?php

namespace App\Http\Requests\LoanProduct;

class StoreLoanProductRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->code ? strtoupper(trim($this->code)) : null,
            'name' => $this->name ? trim($this->name) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'min:3', 'max:100', 'unique:loan_products,name'],
            'code'                    => ['required', 'string', 'max:20', 'alpha_dash', 'unique:loan_products,code'],
            'collateral_type'         => ['required', 'in:vehicle,land,both'],
            'description'             => ['nullable', 'string', 'max:1000'],
            'is_active'               => ['boolean'],

            // Rates
            'interest_rate'           => ['required', 'numeric', 'min:0.01', 'max:100'],
            'min_interest_rate'       => ['nullable', 'numeric', 'min:0.01', 'lt:interest_rate'],
            'max_interest_rate'       => ['nullable', 'numeric', 'max:100', 'gt:interest_rate'],
            'interest_method'         => ['required', 'in:reducing_balance,flat_rate'],

            // Terms
            'min_term_months'         => ['required', 'integer', 'min:1', 'max:120'],
            'max_term_months'         => ['required', 'integer', 'min:1', 'max:120', 'gte:min_term_months'],
            'min_loan_amount'         => ['required', 'numeric', 'min:0'],
            'max_loan_amount'         => ['required', 'numeric', 'gt:min_loan_amount'],
            'max_ltv_percent'         => ['required', 'numeric', 'min:1', 'max:100'],

            // Fees
            'processing_fee_flat'     => ['nullable', 'numeric', 'min:0'],
            'processing_fee_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],

            // Penalties
            'penalty_rate_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
            'penalty_basis'           => ['required', 'in:instalment,outstanding_balance'],
            'grace_period_days'       => ['required', 'integer', 'min:0', 'max:90'],

            // Features
            'allow_early_settlement'  => ['boolean'],
            'early_settlement_method' => ['nullable', 'required_if:allow_early_settlement,true',
                                          'in:prorated,rebate_78,none'],
            'allow_rate_override'     => ['boolean'],
            'require_guarantor'       => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {

            // Rate override bounds must be set if override is allowed
            if ($this->boolean('allow_rate_override')) {
                if (!$this->min_interest_rate) {
                    $v->errors()->add('min_interest_rate',
                        'Minimum interest rate is required when rate override is enabled.'
                    );
                }
                if (!$this->max_interest_rate) {
                    $v->errors()->add('max_interest_rate',
                        'Maximum interest rate is required when rate override is enabled.'
                    );
                }
                if ($this->min_interest_rate && $this->max_interest_rate
                    && $this->min_interest_rate >= $this->max_interest_rate) {
                    $v->errors()->add('max_interest_rate',
                        'Maximum rate must be greater than the minimum rate.'
                    );
                }
            }

            // Penalty rate sanity: unusually high rates deserve a warning (not a hard block)
            // We add it as an info annotation rather than an error
            if ($this->penalty_rate_percent > 20) {
                $v->errors()->add('penalty_rate_percent',
                    "Penalty rate of {$this->penalty_rate_percent}% is unusually high. " .
                    "Please confirm this is correct before saving."
                );
            }

            // Min loan must be meaningful
            if ($this->min_loan_amount < 500) {
                $v->errors()->add('min_loan_amount',
                    'Minimum loan amount should be at least K 500.'
                );
            }

            // LTV must be reasonable
            if ($this->max_ltv_percent > 90) {
                $v->errors()->add('max_ltv_percent',
                    'Maximum LTV should not exceed 90%. A value this high creates significant credit risk.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.unique'                      => 'A loan product with this name already exists.',
            'code.unique'                      => 'A loan product with this code already exists.',
            'code.alpha_dash'                  => 'Product code may only contain letters, numbers, hyphens, and underscores.',
            'interest_rate.required'           => 'Annual interest rate is required.',
            'interest_method.in'               => 'Method must be "Reducing Balance" or "Flat Rate".',
            'min_term_months.required'         => 'Minimum term (months) is required.',
            'max_term_months.gte'              => 'Maximum term must be greater than or equal to the minimum term.',
            'max_loan_amount.gt'               => 'Maximum loan amount must be greater than the minimum.',
            'max_ltv_percent.required'         => 'Maximum LTV percentage is required.',
            'penalty_rate_percent.required'    => 'Penalty rate is required.',
            'penalty_basis.in'                 => 'Penalty basis must be "instalment" or "outstanding_balance".',
            'grace_period_days.max'            => 'Grace period cannot exceed 90 days.',
            'early_settlement_method.required_if' => 'Early settlement method is required when early settlement is enabled.',
            'early_settlement_method.in'       => 'Method must be: Prorated, Rule of 78, or None.',
            'min_interest_rate.lt'             => 'Minimum rate must be less than the default rate.',
            'max_interest_rate.gt'             => 'Maximum rate must be greater than the default rate.',
        ];
    }

    public function attributes(): array
    {
        return [
            'interest_rate'           => 'annual interest rate',
            'min_interest_rate'       => 'minimum interest rate',
            'max_interest_rate'       => 'maximum interest rate',
            'interest_method'         => 'calculation method',
            'min_term_months'         => 'minimum term',
            'max_term_months'         => 'maximum term',
            'min_loan_amount'         => 'minimum loan amount',
            'max_loan_amount'         => 'maximum loan amount',
            'max_ltv_percent'         => 'maximum LTV',
            'processing_fee_flat'     => 'flat processing fee',
            'processing_fee_percent'  => 'percentage processing fee',
            'penalty_rate_percent'    => 'penalty rate',
            'penalty_basis'           => 'penalty basis',
            'grace_period_days'       => 'grace period',
            'allow_early_settlement'  => 'early settlement',
            'early_settlement_method' => 'early settlement method',
            'allow_rate_override'     => 'rate override',
            'require_guarantor'       => 'guarantor requirement',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// UpdateLoanProductRequest
// PUT /api/loan-products/{loanProduct}
// ═══════════════════════════════════════════════════════════════════════════════
