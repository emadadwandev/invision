<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User management
            ['name' => 'users.view', 'group' => 'users', 'description' => 'View users'],
            ['name' => 'users.create', 'group' => 'users', 'description' => 'Create users'],
            ['name' => 'users.update', 'group' => 'users', 'description' => 'Update users'],
            ['name' => 'users.delete', 'group' => 'users', 'description' => 'Delete users'],

            // Team management
            ['name' => 'teams.view', 'group' => 'teams', 'description' => 'View teams'],
            ['name' => 'teams.create', 'group' => 'teams', 'description' => 'Create teams'],
            ['name' => 'teams.update', 'group' => 'teams', 'description' => 'Update teams'],
            ['name' => 'teams.delete', 'group' => 'teams', 'description' => 'Delete teams'],
            ['name' => 'teams.manage-members', 'group' => 'teams', 'description' => 'Manage team members'],

            // Area management
            ['name' => 'areas.view', 'group' => 'areas', 'description' => 'View areas'],
            ['name' => 'areas.create', 'group' => 'areas', 'description' => 'Create areas'],
            ['name' => 'areas.update', 'group' => 'areas', 'description' => 'Update areas'],
            ['name' => 'areas.delete', 'group' => 'areas', 'description' => 'Delete areas'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name']],
                $permData
            );
        }

        // Assign all permissions to SuperAdmin
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role' => UserRole::SuperAdmin->value,
                'permission_id' => $permission->id,
            ]);
        }

        // Assign permissions to Administrator (all except delete)
        $adminPermissions = Permission::whereNotIn('name', ['users.delete', 'teams.delete', 'areas.delete'])->get();
        foreach ($adminPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role' => UserRole::Administrator->value,
                'permission_id' => $permission->id,
            ]);
        }

        // Assign view + manage permissions to TeamLeader
        $leaderPermissions = Permission::whereIn('name', [
            'users.view', 'teams.view', 'teams.update', 'teams.manage-members', 'areas.view',
        ])->get();
        foreach ($leaderPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role' => UserRole::TeamLeader->value,
                'permission_id' => $permission->id,
            ]);
        }
    }
}
