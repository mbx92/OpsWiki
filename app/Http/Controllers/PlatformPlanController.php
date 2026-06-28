<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Support\PlanMarketingCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformPlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Platform/Plans/Index', [
            'plans' => Plan::orderBy('sort_order')->get(),
        ]);
    }

    public function edit(Plan $plan): Response
    {
        return Inertia::render('Platform/Plans/Edit', [
            'plan' => $plan,
            'gateOptions' => PlanMarketingCatalog::allGateOptions(),
            'marketingOptions' => PlanMarketingCatalog::allMarketingOptions(),
            'includedOnAllPlans' => PlanMarketingCatalog::includedOnAllPlans(),
            'defaultMarketingFeatures' => PlanMarketingCatalog::defaultMarketingFeatures($plan->slug),
            'defaultGateKeys' => PlanMarketingCatalog::enabledGateKeysForPlan($plan->slug),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $request->merge([
            'limit_users' => $request->filled('limit_users') ? $request->input('limit_users') : null,
            'limit_pages' => $request->filled('limit_pages') ? $request->input('limit_pages') : null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_monthly_cents' => 'required|integer|min:0',
            'price_yearly_cents' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'gates' => 'nullable|array',
            'gates.*' => 'string|max:64',
            'limit_users' => 'nullable|integer|min:0',
            'limit_pages' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $allowedGateKeys = array_keys(PlanMarketingCatalog::gateDefinitions());
        $gateKeys = collect($validated['gates'] ?? [])
            ->intersect($allowedGateKeys)
            ->values()
            ->all();

        $limits = [
            'users' => isset($validated['limit_users']) ? (int) $validated['limit_users'] : null,
            'pages' => isset($validated['limit_pages']) ? (int) $validated['limit_pages'] : null,
        ];

        $plan->update([
            ...collect($validated)->except(['limit_users', 'limit_pages', 'features', 'gates'])->all(),
            'currency' => strtoupper($validated['currency']),
            'features' => array_values(array_filter($validated['features'] ?? [])),
            'gates' => $gateKeys,
            'limits' => $limits,
        ]);

        return redirect()->route('platform.plans.index')->with('success', 'Plan updated.');
    }
}
