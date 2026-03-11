<?php

namespace App\Http\Requests\Loan;

class StoreGuarantorRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function prepareForValidation(): void
    {
        $updates = [];

        if ($this->phone) {
            $updates['phone'] = $this->normalisePhone($this->phone);
        }

        if ($this->nrc_number && !$this->guarantor_borrower_id) {
            $updates['nrc_number'] = $this->normaliseNrc($this->nrc_number);
        }

        if (!empty($updates)) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        $loan = $this->route('loan');

        return [
            // Either link to an existing borrower OR provide manual details
            'guarantor_borrower_id' => [
                'nullable',
                'exists:borrowers,id',
                // Cannot be the same borrower as the loan applicant
                $loan ? "not_in:{$loan->borrower_id}" : '',
            ],

            // Manual details — required when not linking to existing borrower
            'full_name'    => ['required_without:guarantor_borrower_id', 'nullable', 'string', 'max:200'],
            'nrc_number'   => ['required_without:guarantor_borrower_id', 'nullable', 'string',
                               $this->zambiaNrcRule(), 'max:30'],
            'phone'        => ['required_without:guarantor_borrower_id', 'nullable', 'string',
                               $this->zambiaPhoneRule(), 'max:20'],

            'relationship'  => ['required', 'string', 'max:100'],
            'address'       => ['nullable', 'string', 'max:500'],
            'employer'      => ['nullable', 'string', 'max:150'],
            'monthly_income'=> ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $loan = $this->route('loan');

            if (!$loan) {
                return;
            }

            // Check loan product requires guarantor (informational — not a hard block here,
            // but the loan cannot be approved without one)
            // This is already enforced at approval time; here we just check for duplicates.

            // Prevent the same registered borrower being added twice
            if ($this->guarantor_borrower_id) {
                $alreadyAdded = $loan->guarantors()
                    ->where('guarantor_borrower_id', $this->guarantor_borrower_id)
                    ->exists();

                if ($alreadyAdded) {
                    $v->errors()->add('guarantor_borrower_id',
                        'This person is already listed as a guarantor on this loan.'
                    );
                }

                // A guarantor cannot be a guarantor on more than 3 active loans
                $activeGuaranteedLoans = \App\Models\Guarantor::where('guarantor_borrower_id', $this->guarantor_borrower_id)
                    ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue', 'approved']))
                    ->count();

                if ($activeGuaranteedLoans >= 3) {
                    $v->errors()->add('guarantor_borrower_id',
                        'This borrower is already guaranteeing 3 active loans, which is the maximum permitted.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'guarantor_borrower_id.exists'    => 'The selected borrower does not exist in the system.',
            'guarantor_borrower_id.not_in'    => 'The loan applicant cannot guarantee their own loan.',
            'full_name.required_without'      => 'Full name is required when not linking to a registered borrower.',
            'nrc_number.required_without'     => 'NRC number is required when not linking to a registered borrower.',
            'nrc_number.regex'                => 'NRC must be in the format 123456/78/1.',
            'phone.required_without'          => 'Phone number is required when not linking to a registered borrower.',
            'phone.regex'                     => 'Phone must be a valid Zambian mobile number.',
            'relationship.required'           => 'The guarantor\'s relationship to the borrower is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'guarantor_borrower_id' => 'registered borrower',
            'full_name'             => 'guarantor name',
            'nrc_number'            => 'NRC number',
            'relationship'          => 'relationship',
            'monthly_income'        => 'monthly income',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// UploadDocumentRequest
// POST /api/borrowers/{borrower}/documents
// ═══════════════════════════════════════════════════════════════════════════════
