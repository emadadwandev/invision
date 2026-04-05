<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader,
            UserRole::AccountManager
        );
    }

    public function view(User $user, Team $team): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader,
            UserRole::AccountManager
        );
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator
        );
    }

    public function update(User $user, Team $team): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader
        );
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator
        );
    }

    public function manageMembers(User $user, Team $team): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader
        );
    }
}
