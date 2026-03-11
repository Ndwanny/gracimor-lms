<?php

namespace App\Http\Requests\Loan;

class StoreLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function rules(): array
    {
        return [
            'borrower_id'          => ['required', 'exists:borrowers,id'],
            'loan_product_id'      => ['required', 'exists:loan_products,id'],
            'collateral_asset_id'  => ['required', 'exists:collateral_assets,id'],
            'principal_amount'     => ['required', 'numeric', 'min:1000'],
            'interest_rate'        => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'term_months'          => ['required', 'integer', 'min:1', 'max:60'],
            'first_repayment_date' => ['required', 'date', 'after:today', 'before:' . now()->addYears(5)->toDateString()],
            'disbursement_method'  => ['required', 'in:cash,bank_transfer,mobile_money'],
            'loan_purpose'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {

            $product = LoanProduct::find($this->loan_product_id);

            if (!$product) {
                return; // Already caught by exists rule
            }

            // ── Product must be active ────────────────────────────────────────
            if (!$product->is_active) {
                $v->errors()->add('loan_product_id',
                    "The selected loan product '{$product->name}' is currently inactive and cannot accept new applications."
                );
            }

            // ── Borrower KYC must be verified ─────────────────────────────────
            $borrower = \App\Models\Borrower::find($this->borrower_id);
            if ($borrower && $borrower->kyc_status !== 'verified') {
                $v->errors()->add('borrower_id',
                    "The borrower's KYC has not been verified. Please complete KYC verification before applying for a loan."
                );
            }

            // ── Borrower must not have an existing active loan ─────────────────
            // (configurable — some products may allow multiple)
            if ($borrower && $borrower->loans()->whereIn('status', ['active', 'overdue', 'pending_approval', 'approved'])->exists()) {
                $v->errors()->add('borrower_id',
                    'This borrower already has an active or pending loan. Settle or close it before applying for a new one.'
                );
            }

            // ── Principal within product limits ───────────────────────────────
            if ($this->principal_amount) {
                if ($this->principal_amount < $product->min_loan_amount) {
                    $v->errors()->add('principal_amount',
                        "The minimum loan amount for {$product->name} is K " .
                        number_format($product->min_loan_amount, 2) . "."
                    );
                }
                if ($this->principal_amount > $product->max_loan_amount) {
                    $v->errors()->add('principal_amount',
                        "The maximum loan amount for {$product->name} is K " .
                        number_format($product->max_loan_amount, 2) . "."
                    );
                }
            }

            // ── Term within product limits ────────────────────────────────────
            if ($this->term_months) {
                if ($this->term_months < $product->min_term_months) {
                    $v->errors()->add('term_months',
                        "The minimum term for {$product->name} is {$product->min_term_months} month(s)."
                    );
                }
                if ($this->term_months > $product->max_term_months) {
                    $v->errors()->add('term_months',
                        "The maximum term for {$product->name} is {$product->max_term_months} month(s)."
                    );
                }
            }

            // ── Interest rate override check ──────────────────────────────────
            if ($this->interest_rate) {
                if (!$product->allow_rate_override) {
                    $v->errors()->add('interest_rate',
                        "The selected product does not allow interest rate overrides. Leave this blank to use the default rate of {$product->interest_rate}%."
                    );
                } elseif ($this->interest_rate < $product->min_interest_rate) {
                    $v->errors()->add('interest_rate',
                        "The minimum allowed rate for {$product->name} is {$product->min_interest_rate}%."
                    );
                } elseif ($this->interest_rate > $product->max_interest_rate) {
                    $v->errors()->add('interest_rate',
                        "The maximum allowed rate for {$product->name} is {$product->max_interest_rate}%."
                    );
                }
            }

            // ── LTV check against collateral value ────────────────────────────
            $collateral = CollateralAsset::find($this->collateral_asset_id);
            if ($collateral && $this->principal_amount && $collateral->estimated_value > 0) {

                // Collateral must be available (not pledged to another active loan)
                if ($collateral->status === 'pledged') {
                    $v->errors()->add('collateral_asset_id',
                        "This collateral asset is currently pledged to another active loan and cannot be used."
                    );
                }

                $ltv = ($this->principal_amount / $collateral->estimated_value) * 100;
                if ($ltv > $product->max_ltv_percent) {
                    $maxLoan = $collateral->estimated_value * ($product->max_ltv_percent / 100);
                    $v->errors()->add('principal_amount',
                        "The requested amount exceeds the maximum LTV of {$product->max_ltv_percent}% for this collateral " .
                        "(valued at K " . number_format($collateral->estimated_value, 2) . "). " .
                        "Maximum loan amount: K " . number_format($maxLoan, 2) . "."
                    );
                }

                // Collateral type must match product
                if ($product->collateral_type !== 'both' && $collateral->collateral_type !== $product->collateral_type) {
                    $v->errors()->add('collateral_asset_id',
                        "This product only accepts {$product->collateral_type} collateral, but the selected asset is {$collateral->collateral_type}."
                    );
                }
            }

            // ── First repayment date must be after disbursement window ────────
            if ($this->first_repayment_date) {
                $firstDue = now()->parse($this->first_repayment_date);
                if ($firstDue->diffInDays(now()) < 7) {
                    $v->errors()->add('first_repayment_date',
                        'First repayment date must be at least 7 days from today to allow time for disbursement.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'borrower_id.required'         => 'Please select a borrower.',
            'borrower_id.exists'           => 'The selected borrower does not exist.',
            'loan_product_id.required'     => 'Please select a loan product.',
            'loan_product_id.exists'       => 'The selected loan product does not exist.',
            'collateral_asset_id.required' => 'A collateral asset must be selected for this loan.',
            'collateral_asset_id.exists'   => 'The selected collateral asset does not exist.',
            'principal_amount.required'    => 'Loan amount is required.',
            'principal_amount.numeric'     => 'Loan amount must be a number.',
            'principal_amount.min'         => 'Minimum loan amount is K 1,000.',
            'term_months.required'         => 'Loan term (in months) is required.',
            'term_months.integer'          => 'Loan term must be a whole number of months.',
            'first_repayment_date.required'=> 'First repayment date is required.',
            'first_repayment_date.after'   => 'First repayment date must be in the future.',
            'disbursement_method.required' => 'Please select how the loan will be disbursed.',
            'disbursement_method.in'       => 'Disbursement method must be Cash, Bank Transfer, or Mobile Money.',
        ];
    }

    public function attributes(): array
    {
        return [
            'borrower_id'          => 'borrower',
            'loan_product_id'      => 'loan product',
            'collateral_asset_id'  => 'collateral asset',
            'principal_amount'     => 'loan amount',
            'interest_rate'        => 'interest rate',
            'term_months'          => 'loan term',
            'first_repayment_date' => 'first repayment date',
            'disbursement_method'  => 'disbursement method',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ApproveLoanRequest
// POST /api/loans/{loan}/approve
// ═══════════════════════════════════════════════════════════════════════════════
