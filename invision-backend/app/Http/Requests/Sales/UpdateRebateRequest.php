<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRebateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['sometimes', 'in:percentage,fixed,tiered'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'min_quantity' => ['nullable', 'integer', 'min:0'],
            'max_quantity' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'product_id' => ['nullable', 'exists:products,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
        ];
    }
}
