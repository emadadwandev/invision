<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeamService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Team::with(['parentTeam', 'members']);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (! empty($filters['parent_team_id'])) {
            $query->where('parent_team_id', $filters['parent_team_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);

        return $team->refresh();
    }

    public function delete(Team $team): bool
    {
        return $team->delete();
    }

    public function addMember(Team $team, int $userId, string $position = 'member'): TeamMember
    {
        return TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $userId,
            'position' => $position,
        ]);
    }

    public function removeMember(Team $team, int $userId): bool
    {
        return TeamMember::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function transferMember(int $userId, int $fromTeamId, int $toTeamId, ?string $reason = null, ?int $approvedBy = null): TeamTransfer
    {
        $this->removeMember(Team::findOrFail($fromTeamId), $userId);

        $this->addMember(Team::findOrFail($toTeamId), $userId);

        return TeamTransfer::create([
            'user_id' => $userId,
            'from_team_id' => $fromTeamId,
            'to_team_id' => $toTeamId,
            'reason' => $reason,
            'approved_by' => $approvedBy,
        ]);
    }

    public function getHierarchy(?int $parentId = null): Collection
    {
        return Team::with(['childTeams' => function ($query) {
            $query->with('childTeams');
        }])
            ->where('parent_team_id', $parentId)
            ->get();
    }
}
