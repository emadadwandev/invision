<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutePlanStoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'store' => new StoreResource($this->whenLoaded('store')),
            'day_of_week' => $this->day_of_week,
            'week_number' => $this->week_number,
            'week_start' => $this->week_start?->toDateString(),
            'visit_order' => $this->visit_order,
            'expected_duration_minutes' => $this->expected_duration_minutes,
            'notes' => $this->notes,
        ];
    }
}
