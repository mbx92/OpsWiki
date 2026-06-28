<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $item) {
            Permission::updateOrCreate(
                ['slug' => $item['slug']],
                ['name' => $item['name'], 'group' => $item['group']],
            );
        }

        $roles = [
            [
                'slug' => 'owner',
                'name' => 'Owner',
                'description' => 'Full access to all features and role management.',
                'is_system' => true,
            ],
            [
                'slug' => 'admin',
                'name' => 'Admin',
                'description' => 'Manage content, settings, integrations, and team users. Cannot change role permissions.',
                'is_system' => true,
            ],
            [
                'slug' => 'user',
                'name' => 'User',
                'description' => 'Create and edit own wiki content, inbox, and snippets.',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => $roleData['is_system'],
                ],
            );

            $permissionSlugs = PermissionCatalog::defaultRolePermissions()[$roleData['slug']];
            $permissionIds = Permission::query()
                ->whereIn('slug', $permissionSlugs)
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
