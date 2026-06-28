<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformSubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Subscription::query()
            ->with(['tenant:id,name,slug,status', 'plan:id,name,slug,price_monthly_cents,price_yearly_cents,currency'])
            ->latest('updated_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($plan = $request->get('plan')) {
            $query->whereHas('plan', fn ($q) => $q->where('slug', $plan));
        }

        return Inertia::render('Platform/Billing/Subscriptions/Index', [
            'subscriptions' => $query->paginate(25)->withQueryString(),
            'plans' => Plan::orderBy('sort_order')->get(['id', 'name', 'slug']),
            'filters' => $request->only(['status', 'plan']),
        ]);
    }

    public function update(Request $request, Subscription $subscription, BillingService $billing): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,trialing,suspended,cancelled',
            'billing_interval' => 'required|in:monthly,yearly',
            'current_period_start' => 'nullable|date',
            'current_period_end' => 'nullable|date|after:current_period_start',
            'trial_ends_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'billing_email' => 'nullable|email|max:255',
            'external_customer_id' => 'nullable|string|max:255',
        ]);

        $subscription->update($validated);

        if ($subscription->status === 'trialing' && ! $subscription->trial_ends_at) {
            $subscription->update(['trial_ends_at' => now()->addDays(14)]);
        }

        $billing->ensureSubscriptionPeriod($subscription->fresh());

        return back()->with('success', 'Subscription updated.');
    }
}
