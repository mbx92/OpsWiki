<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\TenantUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformTenantController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Tenant::query()
            ->with(['subscription.plan'])
            ->withCount('users')
            ->latest();

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($plan = $request->get('plan')) {
            $query->whereHas('subscription.plan', fn ($q) => $q->where('slug', $plan));
        }

        return Inertia::render('Platform/Tenants/Index', [
            'tenants' => $query->paginate(20)->withQueryString(),
            'plans' => Plan::orderBy('sort_order')->get(['id', 'name', 'slug']),
            'filters' => $request->only(['q', 'status', 'plan']),
        ]);
    }

    public function show(Tenant $tenant, TenantUsageService $usage): Response
    {
        $tenant->load([
            'subscription.plan',
            'domains',
            'users' => fn ($q) => $q->with('role')->orderBy('name'),
        ]);

        $recentInvoices = Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->with('plan:id,name')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'status' => $invoice->status,
                'total_cents' => $invoice->total_cents,
                'currency' => $invoice->currency,
                'due_at' => $invoice->due_at?->toIso8601String(),
            ]);

        return Inertia::render('Platform/Tenants/Show', [
            'tenant' => $tenant,
            'usage' => $usage->for($tenant),
            'plans' => Plan::orderBy('sort_order')->get(['id', 'name', 'slug', 'limits', 'price_monthly_cents', 'price_yearly_cents', 'currency']),
            'recentInvoices' => $recentInvoices,
        ]);
    }

    public function update(Request $request, Tenant $tenant, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,suspended',
            'plan_id' => 'required|exists:plans,id',
            'subscription_status' => 'required|in:active,trialing,suspended,cancelled',
            'billing_interval' => 'required|in:monthly,yearly',
            'billing_email' => 'nullable|email|max:255',
            'trial_ends_at' => 'nullable|date',
            'current_period_end' => 'nullable|date',
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        $subscription = Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plan_id' => $validated['plan_id'],
                'status' => $validated['subscription_status'],
                'billing_interval' => $validated['billing_interval'],
                'billing_email' => $validated['billing_email'] ?? null,
                'trial_ends_at' => $validated['trial_ends_at'] ?? null,
                'current_period_end' => $validated['current_period_end'] ?? null,
            ],
        );

        $billing->ensureSubscriptionPeriod($subscription->fresh());

        return back()->with('success', 'Workspace updated.');
    }
}
