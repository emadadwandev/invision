<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gps_latitude' => $this->gps_latitude !== null ? (float) $this->gps_latitude : null,
            'gps_longitude' => $this->gps_longitude !== null ? (float) $this->gps_longitude : null,
            'radius_meters' => $this->radius_meters,
            'is_active' => $this->is_active,
            'sector' => new SectorResource($this->whenLoaded('sector')),
            'streets' => StreetResource::collection($this->whenLoaded('streets')),
        ];
    }
}
