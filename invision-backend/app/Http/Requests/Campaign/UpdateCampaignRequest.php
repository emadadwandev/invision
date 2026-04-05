<?php

namespace App\Http\Requests\Campaign;

use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['sometimes', Rule::enum(CampaignType::class)],
            'status' => ['sometimes', Rule::enum(CampaignStatus::class)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'offer_details' => ['nullable', 'array'],
            'reward_details' => ['nullable', 'array'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['exists:stores,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ];
    }
}
