<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'campaign_id' => $this->campaign_id,
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'store_id' => $this->store_id,
            'store' => new StoreResource($this->whenLoaded('store')),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'status' => $this->status,
            'instructions' => $this->instructions,
            'notes' => $this->notes,
            'completed_at' => $this->completed_at,
            'verified_by' => $this->verified_by,
            'verifier' => new UserResource($this->whenLoaded('verifier')),
            'verified_at' => $this->verified_at,
            'rejection_reason' => $this->rejection_reason,
            'photos' => CampaignTaskPhotoResource::collection($this->whenLoaded('photos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
