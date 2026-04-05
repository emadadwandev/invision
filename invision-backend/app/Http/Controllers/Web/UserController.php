<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    private const MOBILE_ROLES = [
        'field_force',
        'promoter',
        'merchandiser',
        'sales_representative',
    ];

    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->only(['search', 'role', 'is_active']);

        // Team leaders only see mobile/field force users
        if ($request->user()->hasRole(UserRole::TeamLeader)) {
            $filters['roles_in'] = self::MOBILE_ROLES;
        }

        $users = $this->userService->list(
            $filters,
            $request->integer('per_page', 15)
        );

        $isTeamLeader = $request->user()->hasRole(UserRole::TeamLeader);

        return view('pages.users.index', compact('users', 'isTeamLeader'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $isTeamLeader = $request->user()->hasRole(UserRole::TeamLeader);

        return view('pages.users.create', compact('isTeamLeader'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->userService->create($request->validated());

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('pages.users.show', compact('user'));
    }

    public function edit(User $user, Request $request): View
    {
        $this->authorize('update', $user);

        $isTeamLeader = $request->user()->hasRole(UserRole::TeamLeader);

        return view('pages.users.edit', compact('user', 'isTeamLeader'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->update($user, $request->validated());

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
