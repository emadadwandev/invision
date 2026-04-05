<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CreateCampaignEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'campaign_task_id' => ['nullable', 'exists:campaign_tasks,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'entry_type' => ['required', 'string', 'in:qr_scan,barcode,coupon,manual'],
            'code' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
