<?php

namespace App\Http\Requests\Penalty;

class WaivePenaltyRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function rules(): array
    {
        return [
            'loan_id'          => ['required', 'exists:loans,id'],
            'scope'            => ['required', 'in:all,instalment'],
            'loan_schedule_id' => ['required_if:scope,instalment', 'nullable', 'exists:loan_schedules,id'],
            'reason'           => ['required', 'in:hardship,error,goodwill,health,death,management_decision,other'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = \App\Models\Loan::find($this->loan_id);

            if (!$loan) {
                return;
            }

            // Confirm there are actually outstanding penalties to waive
            $hasPenalties = $loan->penalties()->where('status', 'outstanding')->exists();
            if (!$hasPenalties) {
                $v->errors()->add('loan_id',
                    'There are no outstanding penalties on this loan to waive.'
                );
            }

            // Verify the specific instalment belongs to this loan
            if ($this->scope === 'instalment' && $this->loan_schedule_id) {
                $scheduleOnLoan = $loan->loanSchedule()
                    ->where('id', $this->loan_schedule_id)
                    ->exists();

                if (!$scheduleOnLoan) {
                    $v->errors()->add('loan_schedule_id',
                        'The selected instalment does not belong to this loan.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'loan_id.required'          => 'Please select a loan.',
            'scope.required'            => 'Specify whether to waive all penalties or a specific instalment.',
            'scope.in'                  => 'Scope must be either "all" or "instalment".',
            'loan_schedule_id.required_if' => 'Please select the specific instalment to waive penalties for.',
            'reason.required'           => 'A waiver reason is required for audit purposes.',
            'reason.in'                 => 'Please select a valid waiver reason from the list.',
        ];
    }

    public function attributes(): array
    {
        return [
            'loan_id'          => 'loan',
            'loan_schedule_id' => 'instalment',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// BulkWaivePenaltiesRequest
// POST /api/penalties/bulk-waive
// ═══════════════════════════════════════════════════════════════════════════════
