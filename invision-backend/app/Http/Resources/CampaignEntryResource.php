<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'campaign_id' => $this->campaign_id,
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'campaign_task_id' => $this->campaign_task_id,
            'store_id' => $this->store_id,
            'store' => new StoreResource($this->whenLoaded('store')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'entry_type' => $this->entry_type,
            'code' => $this->code,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
