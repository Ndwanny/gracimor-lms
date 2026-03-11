<?php

namespace App\Http\Requests\Loan;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class LoanCalculatorRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        // All authenticated staff may use the calculator
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'principal'            => ['required', 'numeric', 'min:1', 'max:99999999'],
            'rate'                 => ['required', 'numeric', 'min:0.01', 'max:100'],
            'term_months'          => ['required', 'integer', 'min:1', 'max:60'],
            'method'               => ['required', 'in:reducing_balance,flat_rate'],
            'first_repayment_date' => ['required', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'principal.required'    => 'Principal amount is required to calculate a schedule.',
            'rate.required'         => 'Interest rate is required.',
            'rate.min'              => 'Interest rate must be greater than 0.',
            'term_months.required'  => 'Loan term (months) is required.',
            'method.in'             => 'Calculation method must be "reducing_balance" or "flat_rate".',
            'first_repayment_date.after' => 'First repayment date must be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'rate'                 => 'annual interest rate',
            'term_months'          => 'loan term',
            'method'               => 'calculation method',
            'first_repayment_date' => 'first repayment date',
        ];
    }
}
