<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        if ($tenant = $this->resolveFromHost($request->getHost())) {
            return $tenant;
        }

        if ($tenantId = $request->session()->get('tenant_id')) {
            return Tenant::find($tenantId);
        }

        if ($user = $request->user()) {
            $membership = $user->tenants()->first();

            if ($membership) {
                return $membership;
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
}
