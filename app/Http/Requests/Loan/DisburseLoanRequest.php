<?php

namespace App\Http\Requests\Loan;

class DisburseLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        // Only CEO and superadmin may disburse — the highest-value action in the system
        return $this->hasRole('superadmin', 'ceo');
    }

    public function rules(): array
    {
        return [
            'disbursement_method'    => ['required', 'in:cash,bank_transfer,mobile_money'],
            'disbursement_reference' => [
                'nullable',
                'required_if:disbursement_method,bank_transfer',
                'required_if:disbursement_method,mobile_money',
                'string', 'max:100',
            ],
            'disburse_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');

            if (!$loan) {
                return;
            }

            if ($loan->status !== 'approved') {
                $v->errors()->add('loan',
                    "Only approved loans can be disbursed (current status: '{$loan->status}')."
                );
            }

            // Collateral must still be available (not repossessed or pledged elsewhere)
            $collateral = $loan->collateralAsset;
            if ($collateral && $collateral->status === 'repossessed') {
                $v->errors()->add('loan',
                    'The collateral asset for this loan has been repossessed. Disbursement cannot proceed.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'disbursement_method.required'             => 'Disbursement method is required.',
            'disbursement_method.in'                   => 'Method must be Cash, Bank Transfer, or Mobile Money.',
            'disbursement_reference.required_if'       => 'A reference number is required for bank transfer and mobile money disbursements.',
        ];
    }

    public function attributes(): array
    {
        return [
            'disbursement_method'    => 'disbursement method',
            'disbursement_reference' => 'reference number',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// EarlySettleLoanRequest
// POST /api/loans/{loan}/early-settle
// ═══════════════════════════════════════════════════════════════════════════════
