<?php

namespace App\Http\Requests\Collateral;

class StoreCollateralRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager', 'officer');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'registration_number' => $this->registration_number
                ? strtoupper(preg_replace('/\s+/', '', $this->registration_number))
                : null,
            'owner_nrc' => $this->owner_nrc
                ? $this->normaliseNrc($this->owner_nrc)
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'collateral_type'       => ['required', 'in:vehicle,land'],
            'asset_description'     => ['required', 'string', 'min:5', 'max:300'],
            'registration_number'   => ['required', 'string', 'max:50', 'unique:collateral_assets,registration_number'],
            'owner_name'            => ['required', 'string', 'max:200'],
            'owner_nrc'             => ['required', 'string', $this->zambiaNrcRule(), 'max:30'],
            'estimated_value'       => ['required', 'numeric', 'min:1000'],
            'valuation_date'        => ['required', 'date', 'before_or_equal:today', 'after:2010-01-01'],
            'valuation_source'      => ['nullable', 'string', 'max:200'],
            'notes'                 => ['nullable', 'string', 'max:1000'],

            // Vehicle-specific
            'make'                  => ['nullable', 'required_if:collateral_type,vehicle', 'string', 'max:100'],
            'model'                 => ['nullable', 'required_if:collateral_type,vehicle', 'string', 'max:100'],
            'year'                  => ['nullable', 'required_if:collateral_type,vehicle', 'integer',
                                        'min:1950', 'max:' . (date('Y') + 1)],
            'colour'                => ['nullable', 'string', 'max:50'],

            // Land-specific
            'plot_number'           => ['nullable', 'required_if:collateral_type,land', 'string', 'max:100'],
            'location_description'  => ['nullable', 'string', 'max:500'],
            'title_deed_number'     => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Zambian vehicle registration format: ZM XXXX / AAA 1234 / etc.
            if ($this->collateral_type === 'vehicle' && $this->registration_number) {
                // Basic sanity: at least 3 chars
                if (strlen($this->registration_number) < 3) {
                    $v->errors()->add('registration_number', 'Vehicle registration number seems too short.');
                }
            }

            // Valuation must not be older than 6 months
            if ($this->valuation_date) {
                $monthsOld = now()->diffInMonths($this->valuation_date);
                if ($monthsOld > 6) {
                    $v->errors()->add('valuation_date',
                        "The valuation is {$monthsOld} months old. Valuations must be within the last 6 months. " .
                        "Please obtain a current valuation."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'collateral_type.required'     => 'Collateral type (vehicle or land) is required.',
            'collateral_type.in'           => 'Collateral type must be either Vehicle or Land.',
            'asset_description.required'   => 'A description of the asset is required.',
            'registration_number.required' => 'Registration or plot number is required.',
            'registration_number.unique'   => 'This registration number is already registered as collateral.',
            'owner_name.required'          => 'Owner name is required.',
            'owner_nrc.required'           => 'Owner NRC number is required.',
            'owner_nrc.regex'              => 'Owner NRC must be in the format 123456/78/1.',
            'estimated_value.required'     => 'Estimated value is required.',
            'estimated_value.min'          => 'Estimated value must be at least K 1,000.',
            'valuation_date.required'      => 'Valuation date is required.',
            'valuation_date.before_or_equal' => 'Valuation date cannot be in the future.',
            'make.required_if'             => 'Vehicle make is required for vehicle collateral.',
            'model.required_if'            => 'Vehicle model is required for vehicle collateral.',
            'year.required_if'             => 'Manufacturing year is required for vehicle collateral.',
            'plot_number.required_if'      => 'Plot number is required for land collateral.',
        ];
    }

    public function attributes(): array
    {
        return [
            'collateral_type'     => 'collateral type',
            'asset_description'   => 'asset description',
            'registration_number' => 'registration / plot number',
            'owner_name'          => "owner's name",
            'owner_nrc'           => "owner's NRC number",
            'estimated_value'     => 'estimated value',
            'valuation_date'      => 'valuation date',
            'title_deed_number'   => 'title deed number',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LogContactRequest
// POST /api/overdue/log-contact
// ═══════════════════════════════════════════════════════════════════════════════
