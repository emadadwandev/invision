<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleRules = ['sometimes', new Enum(UserRole::class)];

        // Team leaders can only assign mobile/field force roles
        if ($this->user()->hasRole(UserRole::TeamLeader)) {
            $mobileValues = collect(UserRole::cases())
                ->filter(fn (UserRole $r) => $r->isMobileUser())
                ->map(fn (UserRole $r) => $r->value)
                ->values()
                ->all();

            $roleRules[] = Rule::in($mobileValues);
        }

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'role' => $roleRules,
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
