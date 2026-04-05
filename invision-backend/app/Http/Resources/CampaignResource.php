<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'budget' => $this->budget,
            'spent' => $this->spent,
            'budget_utilization' => $this->budgetUtilization(),
            'offer_details' => $this->offer_details,
            'reward_details' => $this->reward_details,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'stores' => StoreResource::collection($this->whenLoaded('stores')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'tasks_count' => $this->whenCounted('tasks'),
            'entries_count' => $this->whenCounted('entries'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
