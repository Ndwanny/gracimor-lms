<?php

namespace App\Http\Requests\Borrower;

class StoreBorrowerRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        // All authenticated, active staff may register borrowers
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'nrc_number'     => $this->normaliseNrc($this->nrc_number),
            'phone_primary'  => $this->normalisePhone($this->phone_primary),
            'phone_secondary'=> $this->normalisePhone($this->phone_secondary),
            'work_phone'     => $this->normalisePhone($this->work_phone),
            'first_name'     => $this->first_name  ? ucfirst(strtolower(trim($this->first_name)))  : null,
            'last_name'      => $this->last_name   ? ucfirst(strtolower(trim($this->last_name)))   : null,
            'email'          => $this->email       ? strtolower(trim($this->email))                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name'          => ['required', 'string', 'min:2', 'max:100'],
            'last_name'           => ['required', 'string', 'min:2', 'max:100'],
            'nrc_number'          => ['required', 'string', $this->zambiaNrcRule(), 'unique:borrowers,nrc_number', 'max:30'],
            'date_of_birth'       => ['required', 'date', 'before:today', 'after:1930-01-01'],
            'gender'              => ['required', 'in:male,female,other'],
            'phone_primary'       => ['required', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'phone_secondary'     => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'email'               => ['nullable', 'email:rfc,dns', 'max:150'],
            'residential_address' => ['required', 'string', 'min:10', 'max:500'],
            'city_town'           => ['required', 'string', 'min:2', 'max:100'],
            'employment_status'   => ['required', 'in:employed,self_employed,unemployed,retired'],
            'employer_name'       => ['nullable', 'required_if:employment_status,employed', 'string', 'max:150'],
            'job_title'           => ['nullable', 'string', 'max:100'],
            'monthly_income'      => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'work_phone'          => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'work_address'        => ['nullable', 'string', 'max:500'],
            'assigned_officer_id' => ['nullable', 'exists:users,id'],
            'internal_notes'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Age gate: must be at least 18 years old
            if ($this->date_of_birth) {
                $age = now()->diffInYears($this->date_of_birth);
                if ($age < 18) {
                    $v->errors()->add('date_of_birth', 'The borrower must be at least 18 years old.');
                }
                if ($age > 80) {
                    $v->errors()->add('date_of_birth', 'Date of birth indicates an age over 80. Please verify the date entered.');
                }
            }

            // Employed borrowers must have income declared
            if ($this->employment_status === 'employed' && !$this->monthly_income) {
                $v->errors()->add('monthly_income', 'Monthly income is required for employed borrowers.');
            }

            // Phone numbers must not be the same
            if ($this->phone_primary && $this->phone_secondary
                && $this->phone_primary === $this->phone_secondary) {
                $v->errors()->add('phone_secondary', 'Secondary phone number must be different from the primary number.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nrc_number.required'         => 'The NRC number is required to register a borrower.',
            'nrc_number.regex'             => 'NRC number must be in the format 123456/78/1 (6 digits / 2 digits / 1 digit).',
            'nrc_number.unique'            => 'A borrower with this NRC number is already registered in the system.',
            'phone_primary.required'       => 'A primary phone number is required for contact and SMS reminders.',
            'phone_primary.regex'          => 'Phone number must be a valid Zambian mobile number (e.g. +260977000001 or 0977000001).',
            'phone_secondary.regex'        => 'Secondary phone must be a valid Zambian mobile number.',
            'date_of_birth.required'       => 'Date of birth is required.',
            'date_of_birth.before'         => 'Date of birth must be in the past.',
            'employment_status.in'         => 'Employment status must be one of: Employed, Self-Employed, Unemployed, or Retired.',
            'employer_name.required_if'    => 'Employer name is required when the borrower is employed.',
            'residential_address.min'      => 'Please provide a full residential address (at least 10 characters).',
            'assigned_officer_id.exists'   => 'The selected loan officer does not exist.',
            'email.email'                  => 'Please enter a valid email address.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name'          => 'first name',
            'last_name'           => 'last name',
            'nrc_number'          => 'NRC number',
            'date_of_birth'       => 'date of birth',
            'phone_primary'       => 'primary phone number',
            'phone_secondary'     => 'secondary phone number',
            'residential_address' => 'residential address',
            'city_town'           => 'city / town',
            'employment_status'   => 'employment status',
            'employer_name'       => 'employer name',
            'monthly_income'      => 'monthly income',
            'assigned_officer_id' => 'assigned officer',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// UpdateBorrowerRequest
// PUT /api/borrowers/{borrower}
// ═══════════════════════════════════════════════════════════════════════════════
