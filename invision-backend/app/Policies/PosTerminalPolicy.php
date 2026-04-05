<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PosTerminal;
use App\Models\User;

class PosTerminalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PosTerminal $terminal): bool
    {
        return $user->tenant_id === $terminal->tenant_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::TeamLeader,
        ]);
    }

    public function update(User $user, PosTerminal $terminal): bool
    {
        return $user->tenant_id === $terminal->tenant_id
            && in_array($user->role, [
                UserRole::SuperAdmin,
                UserRole::Admin,
                UserRole::TeamLeader,
            ]);
    }

    public function delete(User $user, PosTerminal $terminal): bool
    {
        return $user->tenant_id === $terminal->tenant_id
            && in_array($user->role, [
                UserRole::SuperAdmin,
                UserRole::Admin,
            ]);
    }
}
