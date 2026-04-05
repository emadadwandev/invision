<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_team_id' => $this->parent_team_id,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'parent_team' => new TeamResource($this->whenLoaded('parentTeam')),
            'child_teams' => TeamResource::collection($this->whenLoaded('childTeams')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'members_count' => $this->whenCounted('members'),
        ];
    }
}
