<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\PlanFeatureCatalog;
use App\Support\TenantContext;
use Illuminate\Validation\ValidationException;

class PlanGateService
{
    public function isGodMode(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function tenant(): Tenant
    {
        return TenantContext::required()->loadMissing('subscription.plan');
    }

    public function plan(): ?Plan
    {
        return $this->tenant()->currentPlan();
    }

    public function planSlug(): string
    {
        return $this->plan()?->slug ?? config('saas.default_plan_slug', 'free');
    }

    public function hasFeature(string $feature): bool
    {
        if ($this->isGodMode()) {
            return true;
        }

        $gates = $this->plan()?->gates;

        if (is_array($gates)) {
            return in_array($feature, $gates, true);
        }

        return PlanFeatureCatalog::planHasFeature($this->planSlug(), $feature);
    }

    public function hasTool(string $toolSlug): bool
    {
        if ($this->isGodMode()) {
            return true;
        }

        return PlanFeatureCatalog::planHasTool($this->planSlug(), $toolSlug);
    }

    public function limit(string $key): ?int
    {
        if ($this->isGodMode()) {
            return null;
        }

        $limit = $this->plan()?->limits[$key] ?? null;

        return is_numeric($limit) ? (int) $limit : null;
    }

    public function usage(string $key): int
    {
        $tenant = $this->tenant();

        return match ($key) {
            'users' => $tenant->users()->count(),
            'pages' => Page::query()->count(),
            default => 0,
        };
    }

    public function withinLimit(string $key, int $delta = 1): bool
    {
        if ($this->isGodMode()) {
            return true;
        }

        $limit = $this->limit($key);

        if ($limit === null) {
            return true;
        }

        return ($this->usage($key) + $delta) <= $limit;
    }

    public function assertFeature(string $feature): void
    {
        if ($this->hasFeature($feature)) {
            return;
        }

        abort(403, PlanFeatureCatalog::upgradeMessage($feature));
    }

    public function assertTool(string $toolSlug): void
    {
        if ($this->hasTool($toolSlug)) {
            return;
        }

        $required = PlanFeatureCatalog::requiredPlanForTool($toolSlug) ?? 'pro';

        abort(403, 'This tool requires the '.ucfirst($required).' plan. Upgrade your workspace to unlock it.');
    }

    /**
     * @throws ValidationException
     */
    public function assertWithinLimit(string $key, int $delta = 1): void
    {
        if ($this->withinLimit($key, $delta)) {
            return;
        }

        $limit = $this->limit($key);

        throw ValidationException::withMessages([
            $key => "Your {$this->plan()?->name} plan allows up to {$limit} {$key}. Upgrade to add more.",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSharedArray(): array
    {
        if ($this->isGodMode()) {
            return [
                'slug' => 'god',
                'name' => 'God mode',
                'god_mode' => true,
                'features' => array_keys(PlanFeatureCatalog::features()),
                'limits' => [
                    'users' => null,
                    'pages' => null,
                ],
                'usage' => [
                    'users' => $this->usage('users'),
                    'pages' => $this->usage('pages'),
                ],
            ];
        }

        $plan = $this->plan();
        $slug = $this->planSlug();
        $gates = $plan?->gates;

        return [
            'slug' => $slug,
            'name' => $plan?->name ?? ucfirst($slug),
            'god_mode' => false,
            'features' => is_array($gates)
                ? array_values($gates)
                : PlanFeatureCatalog::enabledFeaturesForPlan($slug),
            'limits' => [
                'users' => $this->limit('users'),
                'pages' => $this->limit('pages'),
            ],
            'usage' => [
                'users' => $this->usage('users'),
                'pages' => $this->usage('pages'),
            ],
        ];
    }
}
