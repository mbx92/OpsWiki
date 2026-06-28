<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use App\Services\PlanGateService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user()?->toAuthArray(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'tenant' => fn () => TenantContext::get()?->only(['id', 'name', 'slug']),
            'plan' => fn () => TenantContext::get()
                ? app(PlanGateService::class)->toSharedArray()
                : null,
            'saas' => [
                'centralDomain' => config('saas.central_domain'),
                'registrationEnabled' => (bool) config('saas.registration_enabled'),
                'passwordHint' => 'At least 8 characters with uppercase, lowercase, and a number.',
                'featurePlans' => config('saas.features', []),
                'defaultCurrency' => config('saas.default_currency', 'USD'),
            ],
        ];
    }
}
