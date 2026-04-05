<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleRules = ['required', new Enum(UserRole::class)];

        // Team leaders can only create mobile/field force users
        if ($this->user()->hasRole(UserRole::TeamLeader)) {
            $mobileValues = collect(UserRole::cases())
                ->filter(fn (UserRole $r) => $r->isMobileUser())
                ->map(fn (UserRole $r) => $r->value)
                ->values()
                ->all();

            $roleRules[] = Rule::in($mobileValues);
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'role' => $roleRules,
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
