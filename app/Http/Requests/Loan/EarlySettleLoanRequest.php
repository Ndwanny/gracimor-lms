<?php

namespace App\Http\Requests\Loan;

class EarlySettleLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function rules(): array
    {
        return [
            'settlement_amount' => ['required', 'numeric', 'min:0.01'],
            'settlement_method' => ['required', 'in:cash,bank_transfer,mobile_money'],
            'reference'         => ['nullable', 'string', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');

            if (!$loan) {
                return;
            }

            if (!in_array($loan->status, ['active', 'overdue'])) {
                $v->errors()->add('loan',
                    "Early settlement is only available for active or overdue loans (current: '{$loan->status}')."
                );
            }

            // Check product allows early settlement
            if ($loan->loanProduct && !$loan->loanProduct->allow_early_settlement) {
                $v->errors()->add('loan',
                    "The loan product '{$loan->loanProduct->name}' does not permit early settlement."
                );
            }

            // Settlement amount sanity check
            $outstanding = $loan->loanBalance?->total_outstanding ?? 0;
            if ($this->settlement_amount && $this->settlement_amount > $outstanding * 1.1) {
                $v->errors()->add('settlement_amount',
                    'Settlement amount is more than 10% above the outstanding balance (' .
                    'K ' . number_format($outstanding, 2) . '). Please verify the amount.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'settlement_amount.required' => 'Settlement amount is required.',
            'settlement_amount.min'      => 'Settlement amount must be greater than zero.',
            'settlement_method.required' => 'Payment method is required for early settlement.',
            'settlement_method.in'       => 'Payment method must be Cash, Bank Transfer, or Mobile Money.',
        ];
    }

    public function attributes(): array
    {
        return [
            'settlement_amount' => 'settlement amount',
            'settlement_method' => 'payment method',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// WriteOffLoanRequest
// POST /api/loans/{loan}/write-off
// ═══════════════════════════════════════════════════════════════════════════════
