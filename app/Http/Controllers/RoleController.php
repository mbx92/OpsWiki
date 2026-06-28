<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'is_system' => $role->is_system,
                'users_count' => $role->users()->count(),
                'permission_ids' => $role->permissions
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
            ]);

        $permissions = Permission::query()
            ->where('slug', '!=', '*')
            ->orderBy('group')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'group'])
            ->groupBy('group')
            ->map(fn ($items) => $items->map(fn (Permission $permission) => [
                'id' => (int) $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'group' => $permission->group,
            ])->values()->all())
            ->all();

        return Inertia::render('Settings/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'canManage' => auth()->user()?->hasPermission('roles.manage') ?? false,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->slug === 'owner') {
            return back()->with('error', 'Owner permissions cannot be changed.');
        }

        $validated = $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $allowedIds = Permission::query()
            ->where('slug', '!=', '*')
            ->pluck('id');

        $permissionIds = collect($validated['permission_ids'] ?? [])
            ->intersect($allowedIds)
            ->values()
            ->all();

        $role->permissions()->sync($permissionIds);

        return back()->with('success', "Permissions updated for {$role->name}.");
    }
}
