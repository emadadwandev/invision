<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Store $store): bool
    {
        return $user->tenant_id === $store->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::AccountManager,
        );
    }

    public function update(User $user, Store $store): bool
    {
        return $user->tenant_id === $store->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
                UserRole::AccountManager,
            );
    }

    public function delete(User $user, Store $store): bool
    {
        return $user->tenant_id === $store->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
            );
    }
}
