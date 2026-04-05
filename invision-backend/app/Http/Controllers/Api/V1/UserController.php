<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->list(
            $request->only(['search', 'role', 'is_active']),
            $request->integer('per_page', 15)
        );

        return UserResource::collection($users);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user->load('teams'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);

        $user = $this->userService->update($user, $request->validated());

        return new UserResource($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function toggleActive(User $user): UserResource
    {
        $this->authorize('update', $user);

        $user = $this->userService->toggleActive($user);

        return new UserResource($user);
    }
}
