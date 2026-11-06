<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage organizations',
            'view platform dashboard',
            'view organization dashboard',
            'manage organization settings',
            'manage organization users',
            'manage properties',
            'manage tenants',
            'manage reservations',
            'manage contracts',
            'manage billing',
            'manage expenses',
            'manage utilities',
            'manage maintenance',
            'view maintenance',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::query()->firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions([
            'manage organizations',
            'view platform dashboard',
        ]);

        $organizationOwner = Role::query()->firstOrCreate([
            'name' => 'organization-owner',
            'guard_name' => 'web',
        ]);
        $organizationOwner->syncPermissions([
            'view organization dashboard',
            'manage organization settings',
            'manage organization users',
            'manage properties',
            'manage tenants',
            'manage reservations',
            'manage contracts',
            'manage billing',
            'manage expenses',
            'manage utilities',
            'manage maintenance',
            'view reports',
        ]);

        $rolePermissions = [
            'property-manager' => [
                'view organization dashboard',
                'manage properties',
                'manage tenants',
                'manage reservations',
                'manage contracts',
                'manage utilities',
                'manage maintenance',
                'view reports',
            ],
            'receptionist' => [
                'view organization dashboard',
                'manage tenants',
                'manage reservations',
                'view maintenance',
            ],
            'cashier' => [
                'view organization dashboard',
                'manage billing',
            ],
            'maintenance-staff' => [
                'view organization dashboard',
                'manage utilities',
                'manage maintenance',
            ],
            'accountant' => [
                'view organization dashboard',
                'manage expenses',
                'view reports',
            ],
        ];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($assignedPermissions);
        }
    }
}
