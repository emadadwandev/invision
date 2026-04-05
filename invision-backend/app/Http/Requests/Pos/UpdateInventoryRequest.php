<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'on_shelf_quantity' => ['sometimes', 'integer', 'min:0'],
            'warehouse_quantity' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
