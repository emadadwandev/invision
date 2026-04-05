<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutePlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'assigned_to' => $this->assigned_to,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'frequency' => $this->frequency,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'total_stores' => $this->total_stores,
            'stores' => RoutePlanStoreResource::collection($this->whenLoaded('routeStores')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
