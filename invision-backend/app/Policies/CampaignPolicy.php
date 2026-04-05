<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id;
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

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
                UserRole::TeamLeader,
                UserRole::AccountManager,
            );
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->tenant_id === $campaign->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
            );
    }
}
