<?php

namespace App\Http\Requests\Team;

use App\Enums\TeamPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AddTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'position' => ['sometimes', new Enum(TeamPosition::class)],
        ];
    }
}
