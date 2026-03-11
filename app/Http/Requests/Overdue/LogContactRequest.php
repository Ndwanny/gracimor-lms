<?php

namespace App\Http\Requests\Overdue;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LogContactRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function rules(): array
    {
        return [
            'loan_id'             => ['required', 'exists:loans,id'],
            'method'              => ['required', 'in:phone_call,sms,field_visit,email'],
            'outcome'             => ['required', 'in:connected_committed,connected_no_commitment,no_answer,not_reachable,wrong_number,visited_in'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'promise_to_pay_date' => ['nullable', 'date', 'after:today', 'before:' . now()->addMonths(3)->toDateString()],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Promise-to-pay date only makes sense with a positive outcome
            if ($this->promise_to_pay_date
                && !in_array($this->outcome, ['connected_committed', 'visited_in'])) {
                $v->errors()->add('promise_to_pay_date',
                    'A promise-to-pay date should only be set when the borrower has committed to payment.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'loan_id.required'             => 'Please select a loan.',
            'method.required'              => 'Contact method is required.',
            'method.in'                    => 'Method must be: Phone Call, SMS, Field Visit, or Email.',
            'outcome.required'             => 'Contact outcome is required.',
            'outcome.in'                   => 'Please select a valid outcome from the list.',
            'promise_to_pay_date.after'    => 'Promise-to-pay date must be in the future.',
            'promise_to_pay_date.before'   => 'Promise-to-pay date cannot be more than 3 months away.',
        ];
    }
}
