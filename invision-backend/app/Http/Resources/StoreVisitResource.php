<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreVisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'route_instance_id' => $this->route_instance_id,
            'store_id' => $this->store_id,
            'store' => new StoreResource($this->whenLoaded('store')),
            'user_id' => $this->user_id,
            'visit_order' => $this->visit_order,
            'status' => $this->status,
            'checked_in_at' => $this->checked_in_at,
            'checkin_latitude' => $this->checkin_latitude !== null ? (float) $this->checkin_latitude : null,
            'checkin_longitude' => $this->checkin_longitude !== null ? (float) $this->checkin_longitude : null,
            'checkin_qr_code' => $this->checkin_qr_code,
            'checkin_distance_meters' => $this->checkin_distance_meters !== null ? (float) $this->checkin_distance_meters : null,
            'checked_out_at' => $this->checked_out_at,
            'checkout_latitude' => $this->checkout_latitude !== null ? (float) $this->checkout_latitude : null,
            'checkout_longitude' => $this->checkout_longitude !== null ? (float) $this->checkout_longitude : null,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
            'skip_reason' => $this->skip_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
