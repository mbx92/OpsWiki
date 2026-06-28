<?php

namespace App\Http\Middleware;

use App\Services\PlanGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeature
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        app(PlanGateService::class)->assertFeature($feature);

        return $next($request);
    }
}
