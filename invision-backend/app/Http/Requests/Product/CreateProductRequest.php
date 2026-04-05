<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products')->where('tenant_id', $this->user()->tenant_id)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'price_levels' => ['sometimes', 'array'],
            'price_levels.*.level_name' => ['required_with:price_levels', 'string', 'max:100'],
            'price_levels.*.price' => ['required_with:price_levels', 'numeric', 'min:0'],
            'price_levels.*.effective_from' => ['required_with:price_levels', 'date'],
            'price_levels.*.effective_to' => ['nullable', 'date', 'after:price_levels.*.effective_from'],
        ];
    }
}
