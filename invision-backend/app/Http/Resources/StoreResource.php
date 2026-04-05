<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'code' => $this->code,
            'qr_code' => $this->qr_code,
            'category' => $this->category,
            'rank' => $this->rank,
            'gps_latitude' => $this->gps_latitude !== null ? (float) $this->gps_latitude : null,
            'gps_longitude' => $this->gps_longitude !== null ? (float) $this->gps_longitude : null,
            'address' => $this->address,
            'area_id' => $this->area_id,
            'area' => new AreaResource($this->whenLoaded('area')),
            'profile' => $this->profile,
            'is_active' => $this->is_active,
            'contacts' => StoreContactResource::collection($this->whenLoaded('contacts')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
