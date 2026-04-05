<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamService $teamService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Team::class);

        $teams = $this->teamService->list(
            $request->only(['search', 'is_active', 'parent_team_id']),
            $request->integer('per_page', 15)
        );

        return view('pages.teams.index', compact('teams'));
    }

    public function create(): View
    {
        $this->authorize('create', Team::class);

        $parentTeams = Team::where('is_active', true)->get();

        return view('pages.teams.create', compact('parentTeams'));
    }

    public function store(CreateTeamRequest $request): RedirectResponse
    {
        $this->authorize('create', Team::class);

        $this->teamService->create($request->validated());

        return redirect()->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function show(Team $team): View
    {
        $this->authorize('view', $team);

        $team->load(['parentTeam', 'members', 'childTeams']);

        return view('pages.teams.show', compact('team'));
    }

    public function edit(Team $team): View
    {
        $this->authorize('update', $team);

        $parentTeams = Team::where('is_active', true)
            ->where('id', '!=', $team->id)
            ->get();

        return view('pages.teams.edit', compact('team', 'parentTeams'));
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $this->teamService->update($team, $request->validated());

        return redirect()->route('teams.show', $team)
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);

        $this->teamService->delete($team);

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
