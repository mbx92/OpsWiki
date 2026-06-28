<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Support\PlanMarketingCatalog;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UpgradeController extends Controller
{
    public function pro(Request $request): Response
    {
        return $this->render($request, 'pro');
    }

    public function team(Request $request): Response
    {
        return $this->render($request, 'team');
    }

    private function render(Request $request, string $planSlug): Response
    {
        $targetPlan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();
        $tenant = TenantContext::required()->load('subscription.plan');
        $currentPlan = $tenant->currentPlan();
        $currentSlug = $currentPlan?->slug ?? 'free';

        $currentRank = config('saas.tiers.'.$currentSlug, 0);
        $targetRank = config('saas.tiers.'.$planSlug, 0);
        $alreadyIncluded = $currentRank >= $targetRank;

        $feature = $request->query('feature');
        $featureDef = $feature ? (PlanMarketingCatalog::gateDefinitions()[$feature] ?? null) : null;

        $comparisonPlan = Plan::where('slug', 'free')->first();

        return Inertia::render('Upgrade/Show', [
            'targetPlan' => $targetPlan->only([
                'id', 'name', 'slug', 'description', 'price_monthly_cents',
                'price_yearly_cents', 'currency', 'features', 'limits', 'is_popular',
            ]),
            'currentPlan' => $currentPlan?->only(['id', 'name', 'slug', 'description']),
            'comparisonPlan' => $comparisonPlan?->only(['id', 'name', 'slug', 'features', 'limits']),
            'alreadyIncluded' => $alreadyIncluded,
            'requestedFeature' => $feature,
            'requestedFeatureLabel' => $featureDef['label'] ?? null,
            'includedGates' => collect(PlanMarketingCatalog::gatesForPlan($planSlug))
                ->filter(fn (array $gate) => $gate['enabled'])
                ->values()
                ->all(),
            'includedOnAllPlans' => PlanMarketingCatalog::includedOnAllPlans(),
        ]);
    }
}
