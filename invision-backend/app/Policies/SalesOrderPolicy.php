<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return $user->tenant_id === $salesOrder->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
            UserRole::TeamLeader,
            UserRole::AccountManager,
            UserRole::FieldForce,
        );
    }

    public function update(User $user, SalesOrder $salesOrder): bool
    {
        return $user->tenant_id === $salesOrder->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
                UserRole::TeamLeader,
                UserRole::AccountManager,
                UserRole::FieldForce,
            );
    }

    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $user->tenant_id === $salesOrder->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
            );
    }
}
