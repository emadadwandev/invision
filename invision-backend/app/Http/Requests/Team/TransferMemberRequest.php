<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class TransferMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'from_team_id' => ['required', 'integer', 'exists:teams,id'],
            'to_team_id' => ['required', 'integer', 'exists:teams,id', 'different:from_team_id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
