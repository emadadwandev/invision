<?php

namespace App\Services;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantService
{
    public function create(array $data): Tenant
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return Tenant::create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->refresh();
    }

    public function suspend(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => TenantStatus::Suspended]);

        return $tenant->refresh();
    }

    public function activate(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => TenantStatus::Active]);

        return $tenant->refresh();
    }
}
