<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\TenantProvisioningService;
use App\Services\TenantResolver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        abort_unless(config('saas.registration_enabled'), 404);

        return Inertia::render('Auth/Register');
    }

    /**
     * @throws ValidationException
     */
    public function store(
        RegisterRequest $request,
        TenantProvisioningService $provisioning,
        TenantResolver $resolver,
    ): RedirectResponse {
        abort_unless(config('saas.registration_enabled'), 404);

        $request->recordAttempt();

        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $ownerRole->id,
        ]);

        $tenant = $provisioning->createForUser($user, $validated['workspace_name']);

        event(new Registered($user));

        Auth::login($user);
        $resolver->rememberInSession($request, $tenant);

        return redirect(route('dashboard', absolute: false));
    }
}
