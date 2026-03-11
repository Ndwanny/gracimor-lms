<?php

namespace App\Http\Requests\User;

class UpdateUserRequest extends GracimorFormRequest
{
    public function authorize(): bool
    {
        return $this->hasRole('superadmin', 'ceo', 'manager');
    }

    public function prepareForValidation(): void
    {
        $updates = [];
        if ($this->email) {
            $updates['email'] = strtolower(trim($this->email));
        }
        if ($this->phone) {
            $updates['phone'] = $this->normalisePhone($this->phone);
        }
        if (!empty($updates)) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'      => ['sometimes', 'required', 'string', 'min:2', 'max:150'],
            'email'     => ['sometimes', 'required', 'email:rfc', 'max:150',
                            "unique:users,email,{$userId}"],
            'role'      => ['sometimes', 'required',
                            'in:superadmin,ceo,manager,officer,accountant'],
            'phone'     => ['nullable', 'string', $this->zambiaPhoneRule(), 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $targetUser = $this->route('user');

            if (!$targetUser) {
                return;
            }

            // Prevent demotion of the last active superadmin
            if (
                $this->has('role') &&
                $this->role !== 'superadmin' &&
                $targetUser->role === 'superadmin'
            ) {
                $remainingSuperadmins = \App\Models\User::where('role', 'superadmin')
                    ->where('is_active', true)
                    ->where('id', '!=', $targetUser->id)
                    ->count();

                if ($remainingSuperadmins === 0) {
                    $v->errors()->add('role',
                        'Cannot change the role of the last active superadmin. ' .
                        'Please promote another user to superadmin first.'
                    );
                }
            }

            // Manager cannot assign CEO or superadmin roles
            if ($this->has('role') && in_array($this->role, ['ceo', 'superadmin'])
                && $this->hasRole('manager')) {
                $v->errors()->add('role',
                    'Managers cannot assign CEO or Superadmin roles.'
                );
            }

            // Cannot deactivate your own account via this endpoint
            if ($this->has('is_active') && !$this->boolean('is_active')
                && $targetUser->id === Auth::id()) {
                $v->errors()->add('is_active',
                    'You cannot deactivate your own account. Ask another administrator.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already in use by another account.',
            'role.in'      => 'Role must be: superadmin, ceo, manager, officer, or accountant.',
            'phone.regex'  => 'Phone must be a valid Zambian mobile number (e.g. +260977000001).',
        ];
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// UpdateProfileRequest
// PUT /api/users/me/profile  (self-service)
// ═══════════════════════════════════════════════════════════════════════════════
