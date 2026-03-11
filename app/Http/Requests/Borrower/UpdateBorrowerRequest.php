<?php

namespace App\Http\Requests\Borrower;

use App\Models\CollateralAsset;
use App\Models\LoanProduct;
use Illuminate\Support\Facades\Auth;

class UpdateBorrowerRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'nrc_number'      => $this->nrc_number     ? $this->normaliseNrc($this->nrc_number)            : null,
            'phone_primary'   => $this->phone_primary  ? $this->normalisePhone($this->phone_primary)       : null,
            'phone_secondary' => $this->has('phone_secondary') ? $this->normalisePhone($this->phone_secondary) : null,
            'work_phone'      => $this->has('work_phone')       ? $this->normalisePhone($this->work_phone) : null,
            'email'           => $this->email           ? strtolower(trim($this->email))                   : null,
        ], fn ($v) => !is_null($v)));
    }

    public function rules(): array
    {
        $borrowerId = $this->route('borrower')?->id;

        return [
            'first_name'          => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'last_name'           => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'nrc_number'          => ['sometimes', 'required', 'string', $this->zambiaNrcRule(),
                                      "unique:borrowers,nrc_number,{$borrowerId}", 'max:30'],
            'date_of_birth'       => ['sometimes', 'required', 'date', 'before:today', 'after:1930-01-01'],
            'gender'              => ['sometimes', 'required', 'in:male,female,other'],
            'phone_primary'       => ['sometimes', 'required', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'phone_secondary'     => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'email'               => ['nullable', 'email:rfc', 'max:150'],
            'residential_address' => ['sometimes', 'required', 'string', 'min:10', 'max:500'],
            'city_town'           => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'employment_status'   => ['sometimes', 'required', 'in:employed,self_employed,unemployed,retired'],
            'employer_name'       => ['nullable', 'string', 'max:150'],
            'job_title'           => ['nullable', 'string', 'max:100'],
            'monthly_income'      => ['nullable', 'numeric', 'min:0'],
            'work_phone'          => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'work_address'        => ['nullable', 'string', 'max:500'],
            'assigned_officer_id' => ['nullable', 'exists:users,id'],
            'internal_notes'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->date_of_birth) {
                $age = now()->diffInYears($this->date_of_birth);
                if ($age < 18) {
                    $v->errors()->add('date_of_birth', 'The borrower must be at least 18 years old.');
                }
            }
            if ($this->phone_primary && $this->phone_secondary
                && $this->phone_primary === $this->phone_secondary) {
                $v->errors()->add('phone_secondary', 'Secondary phone number must differ from the primary number.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nrc_number.regex'  => 'NRC number must be in the format 123456/78/1.',
            'nrc_number.unique' => 'This NRC number is already registered to another borrower.',
            'phone_primary.regex'   => 'Primary phone must be a valid Zambian mobile number.',
            'phone_secondary.regex' => 'Secondary phone must be a valid Zambian mobile number.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nrc_number'          => 'NRC number',
            'date_of_birth'       => 'date of birth',
            'phone_primary'       => 'primary phone number',
            'phone_secondary'     => 'secondary phone number',
            'employment_status'   => 'employment status',
            'assigned_officer_id' => 'assigned officer',
        ];
    }
}
