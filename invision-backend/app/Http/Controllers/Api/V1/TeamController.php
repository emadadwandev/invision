<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AddTeamMemberRequest;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\TransferMemberRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamService $teamService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Team::class);

        $teams = $this->teamService->list(
            $request->only(['search', 'is_active', 'parent_team_id']),
            $request->integer('per_page', 15)
        );

        return TeamResource::collection($teams);
    }

    public function store(CreateTeamRequest $request): JsonResponse
    {
        $this->authorize('create', Team::class);

        $team = $this->teamService->create($request->validated());

        return (new TeamResource($team))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Team $team): TeamResource
    {
        $this->authorize('view', $team);

        return new TeamResource($team->load(['parentTeam', 'members', 'childTeams']));
    }

    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        $this->authorize('update', $team);

        $team = $this->teamService->update($team, $request->validated());

        return new TeamResource($team);
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $this->teamService->delete($team);

        return response()->json(['message' => 'Team deleted successfully.']);
    }

    public function addMember(AddTeamMemberRequest $request, Team $team): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        $this->teamService->addMember(
            $team,
            $request->validated('user_id'),
            $request->validated('position', 'member')
        );

        return response()->json(['message' => 'Member added successfully.'], 201);
    }

    public function removeMember(Team $team, int $userId): JsonResponse
    {
        $this->authorize('manageMembers', $team);

        $this->teamService->removeMember($team, $userId);

        return response()->json(['message' => 'Member removed successfully.']);
    }

    public function transfer(TransferMemberRequest $request): JsonResponse
    {
        $this->teamService->transferMember(
            $request->validated('user_id'),
            $request->validated('from_team_id'),
            $request->validated('to_team_id'),
            $request->validated('reason'),
            $request->user()->id
        );

        return response()->json(['message' => 'Member transferred successfully.']);
    }

    public function hierarchy(): JsonResponse
    {
        $this->authorize('viewAny', Team::class);

        $hierarchy = $this->teamService->getHierarchy();

        return response()->json([
            'data' => TeamResource::collection($hierarchy),
        ]);
    }
}
