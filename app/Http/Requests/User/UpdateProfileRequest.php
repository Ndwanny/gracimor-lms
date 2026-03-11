<?php

namespace App\Http\Requests\User;

class UpdateProfileRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return Auth::check(); // Any authenticated user may update their own profile
    }

    public function prepareForValidation(): void
    {
        $updates = [];
        if ($this->phone) {
            $updates['phone'] = $this->normalisePhone($this->phone);
        }
        if (!empty($updates)) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'phone'            => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'current_password' => ['required_with:password', 'string'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed',
                                   'regex:/[A-Z]/',
                                   'regex:/[0-9]/'],
            'password_confirmation' => ['required_with:password', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Verify current password if a new one is being set
            if ($this->password && $this->current_password) {
                $user = Auth::user();
                if (!Hash::check($this->current_password, $user->password)) {
                    $v->errors()->add('current_password',
                        'Your current password is incorrect.'
                    );
                }

                // New password must differ from the current one
                if (Hash::check($this->password, $user->password)) {
                    $v->errors()->add('password',
                        'Your new password must be different from your current password.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Your current password is required when setting a new password.',
            'password.min'                   => 'New password must be at least 8 characters.',
            'password.confirmed'             => 'New password and confirmation do not match.',
            'password.regex'                 => 'Password must contain at least one uppercase letter and one number.',
            'password_confirmation.required_with' => 'Please confirm your new password.',
            'phone.regex'                    => 'Phone must be a valid Zambian mobile number.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'current password',
            'password'         => 'new password',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// StoreGuarantorRequest
// POST /api/loans/{loan}/guarantors
// ═══════════════════════════════════════════════════════════════════════════════
