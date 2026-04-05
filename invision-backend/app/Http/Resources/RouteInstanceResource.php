<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'route_plan_id' => $this->route_plan_id,
            'route_plan' => new RoutePlanResource($this->whenLoaded('routePlan')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'route_date' => $this->route_date?->toDateString(),
            'status' => $this->status,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'total_distance_km' => $this->total_distance_km !== null ? (float) $this->total_distance_km : null,
            'total_visits' => $this->total_visits,
            'completed_visits' => $this->completed_visits,
            'completion_percentage' => $this->completionPercentage(),
            'notes' => $this->notes,
            'visits' => StoreVisitResource::collection($this->whenLoaded('visits')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
