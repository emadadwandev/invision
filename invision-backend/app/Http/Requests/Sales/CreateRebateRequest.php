<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class CreateRebateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'in:percentage,fixed,tiered'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_quantity' => ['nullable', 'integer', 'min:0'],
            'max_quantity' => ['nullable', 'integer', 'min:0', 'gte:min_quantity'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'product_id' => ['nullable', 'exists:products,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
        ];
    }
}
