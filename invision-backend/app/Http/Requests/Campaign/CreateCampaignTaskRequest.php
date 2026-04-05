<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CreateCampaignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'assigned_to' => ['required', 'exists:users,id'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
