<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\PlatformStatsService;
use Inertia\Inertia;
use Inertia\Response;

class PlatformDashboardController extends Controller
{
    public function index(PlatformStatsService $stats): Response
    {
        return Inertia::render('Platform/Dashboard', [
            'stats' => $stats->summary(),
            'planBreakdown' => $stats->planBreakdown(),
            'recentTenants' => Tenant::query()
                ->with(['subscription.plan'])
                ->withCount('users')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Tenant $tenant) => $this->tenantRow($tenant)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function tenantRow(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'users_count' => $tenant->users_count,
            'plan' => $tenant->subscription?->plan?->only(['name', 'slug']),
            'subscription_status' => $tenant->subscription?->status,
            'created_at' => $tenant->created_at?->toIso8601String(),
        ];
    }
}
