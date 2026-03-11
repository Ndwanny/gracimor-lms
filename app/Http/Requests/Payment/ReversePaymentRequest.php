<?php

namespace App\Http\Requests\Payment;

class ReversePaymentRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $payment = $this->route('payment');

            if (!$payment) {
                return;
            }

            if ($payment->payment_date->toDateString() !== today()->toDateString()) {
                $v->errors()->add('payment',
                    'Payment reversals are only permitted on the same day the payment was recorded. ' .
                    "This payment was recorded on {$payment->payment_date->format('d M Y')}."
                );
            }

            // Cannot reverse an already-reversed payment
            if ($payment->status === 'reversed') {
                $v->errors()->add('payment', 'This payment has already been reversed.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A reason for the reversal is required.',
            'reason.min'      => 'Please provide a more detailed reason (at least 10 characters).',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// WaivePenaltyRequest
// POST /api/penalties/waive
// ═══════════════════════════════════════════════════════════════════════════════
