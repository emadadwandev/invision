<?php

namespace App\Http\Requests\Pos;

use App\Enums\PosTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePosTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'pos_terminal_id' => ['nullable', 'exists:pos_terminals,id'],
            'type' => ['sometimes', Rule::enum(PosTransactionType::class)],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.barcode_scanned' => ['nullable', 'string'],
        ];
    }
}
