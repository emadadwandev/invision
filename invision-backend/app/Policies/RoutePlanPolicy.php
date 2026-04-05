<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\RoutePlan;
use App\Models\User;

class RoutePlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RoutePlan $plan): bool
    {
        return $user->tenant_id === $plan->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader,
            UserRole::AccountManager,
        );
    }

    public function update(User $user, RoutePlan $plan): bool
    {
        return $user->tenant_id === $plan->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
                UserRole::TeamLeader,
                UserRole::AccountManager,
            );
    }

    public function delete(User $user, RoutePlan $plan): bool
    {
        return $user->tenant_id === $plan->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
            );
    }
}
