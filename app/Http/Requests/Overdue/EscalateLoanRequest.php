<?php

namespace App\Http\Requests\Overdue;

class EscalateLoanRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function rules(): array
    {
        return [
            'type'            => ['required', 'in:legal_notice,external_collector,collateral_repossession,court_proceedings'],
            'assigned_to'     => ['required', 'in:legal_team,debt_recovery_firm,legal_officer'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'notify_borrower' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');

            if (!$loan) {
                return;
            }

            // Loan must be overdue or already legal
            if (!in_array($loan->status, ['overdue', 'legal'])) {
                $v->errors()->add('loan',
                    "Only overdue or legal-status loans can be escalated (current: '{$loan->status}')."
                );
            }

            // Repossession requires the loan to be at least 60 days overdue
            if ($this->type === 'collateral_repossession') {
                $days = $loan->loanBalance?->days_overdue ?? 0;
                if ($days < 60) {
                    $v->errors()->add('type',
                        "Collateral repossession requires the loan to be at least 60 days overdue " .
                        "(currently {$days} days)."
                    );
                }
            }

            // Court proceedings require at least 90 days
            if ($this->type === 'court_proceedings') {
                $days = $loan->loanBalance?->days_overdue ?? 0;
                if ($days < 90) {
                    $v->errors()->add('type',
                        "Court proceedings require the loan to be at least 90 days overdue " .
                        "(currently {$days} days)."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'Escalation type is required.',
            'type.in'              => 'Please select a valid escalation type.',
            'assigned_to.required' => 'Please specify who this case is being assigned to.',
            'assigned_to.in'       => 'Assignment must be: legal team, debt recovery firm, or legal officer.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type'        => 'escalation type',
            'assigned_to' => 'assigned party',
        ];
    }
}
