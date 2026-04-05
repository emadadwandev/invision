<?php

namespace App\Http\Requests\Store;

use App\Enums\ContactType;
use App\Enums\StoreCategory;
use App\Enums\StoreRank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('stores')->where('tenant_id', $this->user()->tenant_id)],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::enum(StoreCategory::class)],
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
