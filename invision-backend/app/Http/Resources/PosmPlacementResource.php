<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosmPlacementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'posm_material_id' => $this->posm_material_id,
            'material' => new PosmMaterialResource($this->whenLoaded('material')),
            'store_id' => $this->store_id,
            'store' => new StoreResource($this->whenLoaded('store')),
            'placed_by' => $this->placed_by,
            'placed_by_user' => new UserResource($this->whenLoaded('placedByUser')),
            'placed_at' => $this->placed_at?->toDateString(),
            'condition' => $this->condition,
            'photo_path' => $this->photo_path,
            'last_checked_at' => $this->last_checked_at?->toDateString(),
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
