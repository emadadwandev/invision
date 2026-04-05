<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosmMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'sku' => $this->sku,
            'quantity_available' => $this->quantity_available,
            'image_path' => $this->image_path,
            'is_active' => $this->is_active,
            'placements_count' => $this->whenCounted('placements'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
