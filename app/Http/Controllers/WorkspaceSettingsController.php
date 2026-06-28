<?php

namespace App\Http\Controllers;

use App\Models\TenantDomain;
use App\Services\PlanGateService;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceSettingsController extends Controller
{
    public function edit(): Response
    {
        $tenant = TenantContext::required()->load(['subscription.plan', 'domains']);

        return Inertia::render('Settings/Workspace', [
            'tenant' => $tenant,
            'centralDomain' => config('saas.central_domain'),
            'subdomainUrl' => $tenant->subdomainUrl(),
            'portalUrl' => $tenant->portalUrl(),
            'portalCentralUrl' => route('portal.tenant', $tenant->slug),
            'dnsInstructions' => [
                'cnameTarget' => config('saas.dns_cname_target'),
                'aRecordIp' => config('saas.dns_a_record_ip'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = TenantContext::required();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tenant->update(['name' => $validated['name']]);

        return back()->with('success', 'Workspace updated.');
    }

    public function storeDomain(Request $request, PlanGateService $planGate): RedirectResponse
    {
        $planGate->assertFeature('custom_domain');

        $tenant = TenantContext::required();

        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:tenant_domains,domain',
        ]);

        $domain = Str::lower(trim($validated['domain']));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        if (Str::contains($domain, config('saas.central_domain'))) {
            return back()->withErrors(['domain' => 'Use a custom domain, not the platform domain.']);
        }

        $isFirst = ! $tenant->domains()->exists();

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'is_primary' => $isFirst,
            'is_verified' => app()->environment('local'),
            'verified_at' => app()->environment('local') ? now() : null,
        ]);

        $message = app()->environment('local')
            ? 'Custom domain added and auto-verified for local development.'
            : 'Domain added. Point DNS to this app, then mark as verified after propagation.';

        return back()->with('success', $message);
    }

    public function verifyDomain(TenantDomain $domain): RedirectResponse
    {
        $tenant = TenantContext::required();
        abort_unless((int) $domain->tenant_id === (int) $tenant->id, 404);

        $domain->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Domain marked as verified.');
    }

    public function destroyDomain(TenantDomain $domain): RedirectResponse
    {
        $tenant = TenantContext::required();
        abort_unless((int) $domain->tenant_id === (int) $tenant->id, 404);

        $wasPrimary = $domain->is_primary;
        $domain->delete();

        if ($wasPrimary) {
            $tenant->domains()->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Domain removed.');
    }
}
