<?php

namespace App\Http\Requests\Store;

use App\Enums\ContactType;
use App\Enums\StoreCategory;
use App\Enums\StoreRank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('stores')->where('tenant_id', $this->user()->tenant_id)->ignore($this->route('store'))],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'category' => ['sometimes', Rule::enum(StoreCategory::class)],
            'rank' => ['sometimes', Rule::enum(StoreRank::class)],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'profile' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'contacts' => ['sometimes', 'array'],
            'contacts.*.name' => ['required_with:contacts', 'string', 'max:255'],
            'contacts.*.phone' => ['required_with:contacts', 'string', 'max:20'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.position' => ['required_with:contacts', Rule::enum(ContactType::class)],
            'contacts.*.is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
