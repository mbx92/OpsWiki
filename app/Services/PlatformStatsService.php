<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

class PlatformStatsService
{
    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        $activeSubscriptions = Subscription::query()
            ->whereIn('status', ['active', 'trialing'])
            ->whereHas('tenant', fn ($q) => $q->where('status', 'active'))
            ->with('plan')
            ->get();

        return [
            'tenants_total' => Tenant::count(),
            'tenants_active' => Tenant::where('status', 'active')->count(),
            'tenants_suspended' => Tenant::where('status', 'suspended')->count(),
            'users_total' => User::count(),
            'super_admins' => User::where('is_super_admin', true)->count(),
            'signups_30d' => Tenant::where('created_at', '>=', now()->subDays(30))->count(),
            'mrr_cents' => $this->mrrCents($activeSubscriptions),
        ];
    }

    /**
     * @return Collection<int, array{plan: Plan, count: int}>
     */
    public function planBreakdown(): Collection
    {
        $counts = Subscription::query()
            ->selectRaw('plan_id, count(*) as total')
            ->whereIn('status', ['active', 'trialing'])
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        return Plan::orderBy('sort_order')->get()->map(fn (Plan $plan) => [
            'plan' => $plan->only(['id', 'name', 'slug', 'price_monthly_cents', 'currency']),
            'count' => (int) ($counts[$plan->id] ?? 0),
        ]);
    }

    /**
     * @param  Collection<int, Subscription>  $subscriptions
     */
    private function mrrCents(Collection $subscriptions): int
    {
        return (int) $subscriptions->sum(fn (Subscription $sub) => $sub->plan?->price_monthly_cents ?? 0);
    }
}
