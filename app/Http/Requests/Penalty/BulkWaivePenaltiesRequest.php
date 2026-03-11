<?php

namespace App\Http\Requests\Penalty;

class BulkWaivePenaltiesRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        // Bulk waiver — CEO/superadmin only
        return $this->hasRole('superadmin', 'ceo');
    }

    public function rules(): array
    {
        return [
            'loan_ids'   => ['required', 'array', 'min:1', 'max:100'],
            'loan_ids.*' => ['integer', 'exists:loans,id'],
            'reason'     => ['required', 'in:hardship,error,goodwill,health,death,management_decision,other'],
            'notes'      => ['nullable', 'string', 'max:1000'],
            'auth_code'  => ['required', 'string', 'size:6'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // All selected loans must have outstanding penalties
            if ($this->loan_ids) {
                $loansWithPenalties = \App\Models\Loan::whereIn('id', $this->loan_ids)
                    ->whereHas('penalties', fn ($q) => $q->where('status', 'outstanding'))
                    ->pluck('id')
                    ->toArray();

                $nopenalties = array_diff($this->loan_ids, $loansWithPenalties);
                if (!empty($nopenalties)) {
                    $v->errors()->add('loan_ids',
                        'The following loan IDs have no outstanding penalties: ' .
                        implode(', ', $nopenalties)
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'loan_ids.required'   => 'Please select at least one loan.',
            'loan_ids.min'        => 'At least one loan must be selected.',
            'loan_ids.max'        => 'No more than 100 loans can be waived in a single batch.',
            'loan_ids.*.exists'   => 'One or more selected loans do not exist.',
            'reason.required'     => 'A waiver reason is required for audit purposes.',
            'auth_code.required'  => 'Your 6-digit authorisation code is required for bulk waivers.',
            'auth_code.size'      => 'Authorisation code must be exactly 6 characters.',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// StoreCollateralRequest
// POST /api/collateral
// ═══════════════════════════════════════════════════════════════════════════════
