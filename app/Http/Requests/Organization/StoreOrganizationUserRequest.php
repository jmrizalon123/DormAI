<?php

namespace App\Http\Requests\Organization;

use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreOrganizationUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'role' => ['required', 'string', Rule::in([
                'property-manager',
                'receptionist',
                'cashier',
                'maintenance-staff',
                'accountant',
            ])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_type' => UserAccountType::Staff->value,
        ]);
    }
}
