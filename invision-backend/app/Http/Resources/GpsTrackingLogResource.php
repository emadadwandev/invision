<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpsTrackingLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'route_instance_id' => $this->route_instance_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy_meters' => $this->accuracy_meters,
            'speed_kmh' => $this->speed_kmh,
            'bearing' => $this->bearing,
            'recorded_at' => $this->recorded_at,
        ];
    }
}
