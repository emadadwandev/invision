<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'exists:stores,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
