<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    private const MOBILE_ROLES = [
        UserRole::FieldForce,
        UserRole::Promoter,
        UserRole::Merchandiser,
        UserRole::SalesRepresentative,
    ];

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader
        );
    }

    public function view(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return true;
        }

        return $authUser->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader
        );
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader
        );
    }

    public function update(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return true;
        }

        if ($authUser->hasAnyRole(UserRole::SuperAdmin, UserRole::Administrator)) {
            return true;
        }

        // Team leaders can update mobile/field force users only
        if ($authUser->hasRole(UserRole::TeamLeader)) {
            return in_array($user->role, self::MOBILE_ROLES, true);
        }

        return false;
    }

    public function delete(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return false;
        }

        if ($authUser->hasAnyRole(UserRole::SuperAdmin, UserRole::Administrator)) {
            return true;
        }

        // Team leaders can delete mobile/field force users only
        if ($authUser->hasRole(UserRole::TeamLeader)) {
            return in_array($user->role, self::MOBILE_ROLES, true);
        }

        return false;
    }
}
