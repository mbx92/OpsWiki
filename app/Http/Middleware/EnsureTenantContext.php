<?php

namespace App\Http\Middleware;

use App\Services\TenantResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function __construct(private TenantResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! TenantContext::get()) {
            $tenant = $this->resolver->defaultTenant();

            if ($tenant) {
                TenantContext::set($tenant);
            }
        }

        if (! TenantContext::get()) {
            abort(404, 'Workspace not found.');
        }

        return $next($request);
    }
}
