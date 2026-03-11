<?php

namespace App\Http\Requests\Loan;

class WriteOffLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        // Write-off is an irreversible action — CEO and superadmin only
        return $this->hasRole('superadmin', 'ceo');
    }

    public function rules(): array
    {
        return [
            'reason'       => ['required', 'string', 'min:20', 'max:1000'],
            'auth_pin'     => ['required', 'string', 'size:6'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');

            if ($loan && !in_array($loan->status, ['overdue', 'legal'])) {
                $v->errors()->add('loan',
                    "Only overdue or legally-escalated loans can be written off (current: '{$loan->status}')."
                );
            }

            // PAR 90 minimum — loans must be at least 90 days overdue to write off
            if ($loan && ($loan->loanBalance?->days_overdue ?? 0) < 90) {
                $days = $loan->loanBalance?->days_overdue ?? 0;
                $v->errors()->add('loan',
                    "Write-off requires the loan to be at least 90 days overdue. " .
                    "This loan is currently {$days} days overdue."
                );
            }

            // Verify the authorisation PIN matches the current user's stored PIN
            $user = Auth::user();
            if ($user && !password_verify($this->auth_pin, $user->approval_pin ?? '')) {
                $v->errors()->add('auth_pin', 'Authorisation PIN is incorrect.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A write-off justification is required.',
            'reason.min'      => 'Please provide a detailed reason (at least 20 characters).',
            'auth_pin.required' => 'Your 6-digit authorisation PIN is required to confirm a write-off.',
            'auth_pin.size'   => 'Authorisation PIN must be exactly 6 digits.',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanCalculatorRequest
// POST /api/loans/calculate  (no loan created — preview only)
// ═══════════════════════════════════════════════════════════════════════════════
