<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(
            UserRole::SuperAdmin,
            UserRole::Administrator,
        );
    }

    public function update(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
            );
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id
            && $user->hasAnyRole(
                UserRole::SuperAdmin,
                UserRole::Administrator,
            );
    }
}
