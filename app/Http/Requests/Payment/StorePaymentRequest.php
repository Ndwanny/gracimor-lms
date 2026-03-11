<?php

namespace App\Http\Requests\Payment;

class StorePaymentRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer', 'accountant');
    }

    public function rules(): array
    {
        return [
            'loan_id'        => ['required', 'exists:loans,id'],
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'payment_method' => ['required', 'in:cash,bank_transfer,mobile_money,cheque'],
            'payment_date'   => ['required', 'date', 'before_or_equal:today', 'after:2020-01-01'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = Loan::find($this->loan_id);

            if (!$loan) {
                return;
            }

            // Loan must be active or overdue
            if (!in_array($loan->status, ['active', 'overdue'])) {
                $v->errors()->add('loan_id',
                    "Payments cannot be recorded against a loan with status '{$loan->status}'. " .
                    "Only active or overdue loans accept payments."
                );
            }

            // Amount must not exceed outstanding balance by more than 5%
            // (overpayments should use early settlement instead)
            $outstanding = $loan->loanBalance?->total_outstanding ?? 0;
            if ($this->amount && $outstanding > 0 && $this->amount > $outstanding * 1.05) {
                $v->errors()->add('amount',
                    'Payment amount (K ' . number_format($this->amount, 2) . ') significantly exceeds ' .
                    'the outstanding balance (K ' . number_format($outstanding, 2) . '). ' .
                    'For full settlement, use the Early Settlement feature instead.'
                );
            }

            // Bank transfer / mobile money requires a reference
            if (in_array($this->payment_method, ['bank_transfer', 'mobile_money']) && !$this->reference) {
                $v->errors()->add('reference',
                    'A reference number is required for bank transfer and mobile money payments.'
                );
            }

            // Cheque requires a reference (cheque number)
            if ($this->payment_method === 'cheque' && !$this->reference) {
                $v->errors()->add('reference', 'A cheque number is required for cheque payments.');
            }

            // Future payments (backdated beyond 30 days) require manager role
            $paymentDate = now()->parse($this->payment_date);
            $daysAgo = now()->diffInDays($paymentDate);
            if ($daysAgo > 30 && !$this->hasRole('superadmin', 'ceo', 'manager')) {
                $v->errors()->add('payment_date',
                    'Backdated payments older than 30 days require manager authorisation.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'loan_id.required'        => 'Please select a loan to record the payment against.',
            'loan_id.exists'          => 'The selected loan does not exist.',
            'amount.required'         => 'Payment amount is required.',
            'amount.min'              => 'Payment amount must be greater than zero.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in'       => 'Method must be: Cash, Bank Transfer, Mobile Money, or Cheque.',
            'payment_date.required'   => 'Payment date is required.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'payment_date.after'      => 'Payment date seems too far in the past. Please verify.',
        ];
    }

    public function attributes(): array
    {
        return [
            'loan_id'        => 'loan',
            'payment_method' => 'payment method',
            'payment_date'   => 'payment date',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// ReversePaymentRequest
// DELETE /api/payments/{payment}
// ═══════════════════════════════════════════════════════════════════════════════
