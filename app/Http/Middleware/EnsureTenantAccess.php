<?php

namespace App\Http\Middleware;

use App\Services\TenantResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function __construct(private TenantResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('platform.*')) {
            return $next($request);
        }

        $tenant = TenantContext::get() ?? $this->resolver->defaultTenant();

        if (! $tenant) {
            abort(404, 'Workspace not found.');
        }

        TenantContext::set($tenant);

        // Keep session aligned (fixes stale tenant_id after resolver corrections).
        if ($request->hasSession()) {
            $request->session()->put('tenant_id', $tenant->id);
        }

        $user = $request->user();

        if ($user && ! $user->isSuperAdmin()) {
            $isMember = $user->tenants()->where('tenants.id', $tenant->id)->exists();

            if (! $isMember) {
                abort(403, 'You do not have access to this workspace.');
            }
        }

        return $next($request);
    }
}
