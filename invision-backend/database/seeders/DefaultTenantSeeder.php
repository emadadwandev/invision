<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create default tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'invision-default'],
            [
                'name' => 'Invision Default',
                'status' => TenantStatus::Active,
                'plan' => 'enterprise',
            ]
        );

        // Create super admin user
        User::firstOrCreate(
            ['email' => 'admin@invision.test'],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a demo team leader
        User::firstOrCreate(
            ['email' => 'leader@invision.test'],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Team',
                'last_name' => 'Leader',
                'password' => Hash::make('password'),
                'role' => UserRole::TeamLeader,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a demo field force user
        User::firstOrCreate(
            ['email' => 'field@invision.test'],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Field',
                'last_name' => 'Force',
                'password' => Hash::make('password'),
                'role' => UserRole::FieldForce,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
