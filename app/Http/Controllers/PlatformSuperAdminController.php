<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformSuperAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->get('q'));

        $superAdmins = User::query()
            ->where('is_super_admin', true)
            ->with('role:id,name,slug')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id', 'is_active', 'created_at']);

        $candidates = collect();
        if ($search !== '') {
            $candidates = User::query()
                ->where('is_super_admin', false)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'email']);
        }

        return Inertia::render('Platform/SuperAdmins/Index', [
            'superAdmins' => $superAdmins,
            'candidates' => $candidates,
            'filters' => ['q' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->is_super_admin) {
            return back()->with('error', 'User is already a super admin.');
        }

        $user->forceFill(['is_super_admin' => true])->save();

        return back()->with('success', "{$user->name} is now a super admin.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()?->id) {
            return back()->with('error', 'You cannot remove your own super admin access.');
        }

        if (! $user->is_super_admin) {
            return back()->with('error', 'User is not a super admin.');
        }

        $superAdminCount = User::where('is_super_admin', true)->count();
        if ($superAdminCount <= 1) {
            return back()->with('error', 'At least one super admin must remain.');
        }

        $user->forceFill(['is_super_admin' => false])->save();

        return back()->with('success', 'Super admin access removed.');
    }
}
