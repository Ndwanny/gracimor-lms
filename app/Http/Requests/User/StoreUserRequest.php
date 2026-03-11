<?php

namespace App\Http\Requests\User;

class StoreUserRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'name'  => $this->name  ? trim($this->name)              : null,
            'phone' => $this->phone ? $this->normalisePhone($this->phone) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:150'],
            'email'    => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'role'     => ['required', 'in:superadmin,ceo,manager,officer,accountant'],
            'phone'    => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'password' => ['required', 'string', 'min:8',
                           'regex:/[A-Z]/',    // at least one uppercase
                           'regex:/[0-9]/',    // at least one digit
                          ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Only superadmin may create another superadmin
            if ($this->role === 'superadmin' && !$this->hasRole('superadmin')) {
                $v->errors()->add('role',
                    'Only a superadmin may create another superadmin account.'
                );
            }

            // Manager cannot create CEO accounts
            if ($this->role === 'ceo' && $this->hasRole('manager')) {
                $v->errors()->add('role',
                    'Managers cannot create CEO accounts.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Full name is required.',
            'email.required'      => 'Email address is required.',
            'email.email'         => 'Please enter a valid email address.',
            'email.unique'        => 'This email address is already registered.',
            'role.required'       => 'A role must be assigned to the new user.',
            'role.in'             => 'Role must be: Super Admin, CEO, Manager, Officer, or Accountant.',
            'phone.regex'         => 'Phone must be a valid Zambian mobile number.',
            'password.required'   => 'A temporary password is required.',
            'password.min'        => 'Password must be at least 8 characters.',
            'password.regex'      => 'Password must contain at least one uppercase letter and one number.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'  => 'full name',
            'email' => 'email address',
            'role'  => 'user role',
            'phone' => 'phone number',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// UpdateUserRequest
// PUT /api/users/{user}
// ═══════════════════════════════════════════════════════════════════════════════
