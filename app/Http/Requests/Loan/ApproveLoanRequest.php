<?php

namespace App\Http\Requests\Loan;

class ApproveLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function rules(): array
    {
        return [
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');

            if (!$loan) {
                return;
            }

            if ($loan->status !== 'pending_approval') {
                $v->errors()->add('loan',
                    "This loan cannot be approved — its current status is '{$loan->status}'. " .
                    "Only loans with status 'pending_approval' can be approved."
                );
            }

            // Prevent self-approval: officer who applied cannot also approve
            if ($loan->applied_by === Auth::id()) {
                $v->errors()->add('loan',
                    'You cannot approve a loan that you personally submitted. A different manager must approve it.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'approval_notes.max' => 'Approval notes must not exceed 1,000 characters.',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// RejectLoanRequest
// POST /api/loans/{loan}/reject
// ═══════════════════════════════════════════════════════════════════════════════
