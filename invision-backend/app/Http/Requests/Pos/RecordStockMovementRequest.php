<?php

namespace App\Http\Requests\Pos;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
