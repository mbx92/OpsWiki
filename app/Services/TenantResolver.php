<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        if ($tenant = $this->resolveFromHost($request->getHost())) {
            return $this->ensureAccessible($request, $tenant);
        }

        // Respect the user's session-selected tenant first.
        // Previously this ran after the central-host default fallback,
        // which locked every user into the default workspace.
        if ($tenantId = $request->session()->get('tenant_id')) {
            $sessionTenant = Tenant::find($tenantId);

            if ($sessionTenant && $this->canAccess($request->user(), $sessionTenant)) {
                return $sessionTenant;
            }
        }

        if ($user = $request->user()) {
            // Let the user's own membership take priority.
            // Skip the default workspace unless the user has no other memberships.
            $ownTenants = $user->tenants()
                ->where('tenants.slug', '!=', config('saas.default_tenant_slug'))
                ->orderBy('tenant_user.id')
                ->get();

            if ($ownTenants->isNotEmpty()) {
                return $ownTenants->first();
            }

            $default = $this->defaultTenant();

            if ($default && $user->tenants()->where('tenants.id', $default->id)->exists()) {
                return $default;
            }
        }

        // Central domain fallback: only for unauthenticated visitors or
        // users with no membership at all.
        if ($this->isCentralHost($request->getHost())) {
            if ($default = $this->defaultTenant()) {
                return $default;
            }
        }

        return $this->defaultTenant();
    }

    public function resolveFromHost(string $host): ?Tenant
    {
        $host = Str::lower($host);

        $domain = TenantDomain::query()
            ->where('domain', $host)
            ->where('is_verified', true)
            ->first();

        if ($domain) {
            return $domain->tenant;
        }

        $central = Str::lower((string) config('saas.central_domain'));

        if ($host === $central || $host === 'localhost' || $host === '127.0.0.1') {
            return null;
        }

        if (Str::endsWith($host, '.'.$central)) {
            $subdomain = Str::before($host, '.'.$central);

            if ($subdomain !== '' && $subdomain !== 'www') {
                return Tenant::where('slug', $subdomain)->first();
            }
        }

        return null;
    }

    public function defaultTenant(): ?Tenant
    {
        $slug = config('saas.default_tenant_slug');

        return $slug ? Tenant::where('slug', $slug)->first() : null;
    }

    public function apply(Request $request): void
    {
        TenantContext::set($this->resolve($request));
    }

    public function rememberInSession(Request $request, Tenant $tenant): void
    {
        $request->session()->put('tenant_id', $tenant->id);
        TenantContext::set($tenant);
    }

    public function isCentralHost(string $host): bool
    {
        $host = Str::lower($host);
        $central = Str::lower((string) config('saas.central_domain'));

        return $host === $central || $host === 'localhost' || $host === '127.0.0.1';
    }

    private function ensureAccessible(Request $request, Tenant $tenant): ?Tenant
    {
        if ($this->canAccess($request->user(), $tenant)) {
            return $tenant;
        }

        return $this->defaultTenant();
    }

    private function canAccess(?User $user, Tenant $tenant): bool
    {
        if (! $user) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }
}
