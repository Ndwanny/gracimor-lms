<?php

namespace App\Http\Requests\Loan;

class RejectLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');
            if ($loan && !in_array($loan->status, ['pending_approval', 'pending_documents'])) {
                $v->errors()->add('loan',
                    "This loan cannot be rejected at this stage (current status: '{$loan->status}')."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'A reason for rejection is required.',
            'rejection_reason.min'      => 'Please provide a more detailed rejection reason (at least 10 characters).',
        ];
    }

    public function attributes(): array
    {
        return ['rejection_reason' => 'rejection reason'];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// DisburseLoanRequest
// POST /api/loans/{loan}/disburse
// ═══════════════════════════════════════════════════════════════════════════════
