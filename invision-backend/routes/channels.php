<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Tenant-scoped tracking channel (command center live GPS)
Broadcast::channel('tenant.{tenantId}.tracking', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});

// User-scoped notifications channel
Broadcast::channel('user.{userId}.notifications', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Tenant-scoped visit status channel
Broadcast::channel('tenant.{tenantId}.visits', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});

// Tenant-scoped duty status channel
Broadcast::channel('tenant.{tenantId}.duty', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId;
});
