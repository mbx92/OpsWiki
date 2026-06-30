<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\PlanGateService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Users/Index', [
            'users' => $this->tenantUsersQuery()
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email', 'users.role_id', 'users.is_active', 'users.created_at']),
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Settings/Users/Create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(Request $request, PlanGateService $planGate): RedirectResponse
    {
        $planGate->assertWithinLimit('users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'is_active' => 'boolean',
        ]);

        $this->assertAssignableRole((int) $validated['role_id']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        TenantContext::required()->users()->syncWithoutDetaching([
            $user->id => ['role' => 'member'],
        ]);

        return redirect()->route('settings.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): Response
    {
        $this->assertTenantMember($user);

        return Inertia::render('Settings/Users/Edit', [
            'user' => $user->load('role'),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->assertTenantMember($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'is_active' => 'boolean',
        ]);

        $this->assertAssignableRole((int) $validated['role_id'], $user);
        $this->assertCanModifyUser($user, (int) $validated['role_id'], (bool) ($validated['is_active'] ?? true));

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('settings.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->assertTenantMember($user);
        $this->assertCanDeleteUser($user);

        TenantContext::required()->users()->detach($user->id);

        if (! $user->tenants()->exists()) {
            $user->delete();
        }

        return redirect()->route('settings.users.index')->with('success', 'User removed from workspace.');
    }

    private function assertTenantMember(User $user): void
    {
        $exists = $this->tenantUsersQuery()->where('users.id', $user->id)->exists();

        if (! $exists) {
            abort(404);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User, \App\Models\Tenant>
     */
    private function tenantUsersQuery()
    {
        return TenantContext::required()->users()->with('role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    private function assignableRoles()
    {
        $query = Role::query()->orderBy('name');

        if (! auth()->user()?->hasPermission('roles.manage')) {
            $query->where('slug', '!=', 'owner');
        }

        return $query->get(['id', 'name', 'slug', 'description']);
    }

    private function assertAssignableRole(int $roleId, ?User $existing = null): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->slug === 'owner' && ! auth()->user()?->hasPermission('roles.manage')) {
            abort(403, 'Only owners can assign the owner role.');
        }

        if ($existing?->isOwner() && $role->slug !== 'owner' && ! auth()->user()?->hasPermission('roles.manage')) {
            abort(403, 'Only owners can change an owner account role.');
        }
    }

    private function assertCanModifyUser(User $user, int $roleId, bool $isActive): void
    {
        if ($user->id === auth()->id() && ! $isActive) {
            abort(403, 'You cannot deactivate your own account.');
        }

        if ($user->isOwner() && $roleId !== $user->role_id) {
            $ownerCount = $this->tenantUsersQuery()->whereHas('role', fn ($q) => $q->where('slug', 'owner'))->count();
            if ($ownerCount <= 1) {
                abort(403, 'At least one owner account must remain.');
            }
        }

        if ($user->id === auth()->id() && $roleId !== $user->role_id && ! auth()->user()?->hasPermission('roles.manage')) {
            abort(403, 'You cannot change your own role.');
        }
    }

    private function assertCanDeleteUser(User $user): void
    {
        if ($user->id === auth()->id()) {
            abort(403, 'You cannot delete your own account.');
        }

        if ($user->isOwner()) {
            $ownerCount = $this->tenantUsersQuery()->whereHas('role', fn ($q) => $q->where('slug', 'owner'))->count();
            if ($ownerCount <= 1) {
                abort(403, 'At least one owner account must remain.');
            }
        }
    }
}
