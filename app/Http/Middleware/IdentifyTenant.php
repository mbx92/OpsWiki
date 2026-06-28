<?php

namespace App\Http\Middleware;

use App\Services\TenantResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(private TenantResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->resolver->apply($request);

        if ($request->user() && ($tenant = TenantContext::get())) {
            $this->resolver->rememberInSession($request, $tenant);
        }

        return $next($request);
    }
}
