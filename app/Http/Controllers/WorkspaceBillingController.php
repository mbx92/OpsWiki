<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Support\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceBillingController extends Controller
{
    public function index(): Response
    {
        $tenant = TenantContext::required()->load(['subscription.plan']);

        $invoices = Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->with('plan:id,name')
            ->latest()
            ->limit(24)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'status' => $invoice->status,
                'total_cents' => $invoice->total_cents,
                'currency' => $invoice->currency,
                'due_at' => $invoice->due_at?->toIso8601String(),
                'paid_at' => $invoice->paid_at?->toIso8601String(),
                'created_at' => $invoice->created_at?->toIso8601String(),
                'plan' => $invoice->plan?->only(['name']),
            ]);

        return Inertia::render('Settings/Billing/Index', [
            'tenant' => $tenant,
            'invoices' => $invoices,
        ]);
    }
}
