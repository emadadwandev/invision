<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePosTerminalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'exists:stores,id'],
            'terminal_code' => ['sometimes', 'string', 'max:50', 'unique:pos_terminals,terminal_code,' . $this->route('posTerminal')?->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
