<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\PlatformBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformInvoiceController extends Controller
{
    public function index(Request $request, PlatformBillingService $billing): Response
    {
        $query = Invoice::query()
            ->with(['tenant:id,name,slug', 'plan:id,name'])
            ->latest();

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'ilike', "%{$search}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('name', 'ilike', "%{$search}%")
                        ->orWhere('slug', 'ilike', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($tenantId = $request->get('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        return Inertia::render('Platform/Billing/Invoices/Index', [
            'invoices' => $query->paginate(20)->through(fn (Invoice $invoice) => $billing->invoiceRow($invoice)),
            'filters' => $request->only(['q', 'status', 'tenant_id']),
            'statusOptions' => [
                Invoice::STATUS_OPEN,
                Invoice::STATUS_PAID,
                Invoice::STATUS_OVERDUE,
                Invoice::STATUS_DRAFT,
                Invoice::STATUS_VOID,
            ],
        ]);
    }

    public function show(Invoice $invoice, PlatformBillingService $billing): Response
    {
        $invoice->load(['tenant', 'plan', 'subscription', 'payments.recordedBy:id,name']);

        return Inertia::render('Platform/Billing/Invoices/Show', [
            'invoice' => [
                ...$billing->invoiceRow($invoice),
                'subtotal_cents' => $invoice->subtotal_cents,
                'tax_cents' => $invoice->tax_cents,
                'notes' => $invoice->notes,
                'line_items' => $invoice->line_items ?? [],
                'period_start' => $invoice->period_start?->toIso8601String(),
                'period_end' => $invoice->period_end?->toIso8601String(),
            ],
            'payments' => $invoice->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'amount_cents' => $payment->amount_cents,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'status' => $payment->status,
                'reference' => $payment->reference,
                'notes' => $payment->notes,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'recorded_by' => $payment->recordedBy?->only(['id', 'name']),
            ]),
        ]);
    }

    public function store(Request $request, BillingService $billing, ?Tenant $tenant = null): RedirectResponse
    {
        if ($tenant) {
            $request->merge(['tenant_id' => $tenant->id]);
        }

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'nullable|exists:plans,id',
            'billing_interval' => 'nullable|in:monthly,yearly,one_time',
            'total_cents' => 'nullable|integer|min:0',
            'due_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $invoice = $billing->createInvoice($tenant, $validated);

        return redirect()
            ->route('platform.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->number} created.");
    }

    public function recordPayment(Request $request, Invoice $invoice, BillingService $billing): RedirectResponse
    {
        if ($invoice->status === Invoice::STATUS_VOID) {
            return back()->with('error', 'Cannot record payment on a void invoice.');
        }

        $validated = $request->validate([
            'amount_cents' => 'required|integer|min:1',
            'method' => 'required|in:manual,bank_transfer,card,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'paid_at' => 'nullable|date',
        ]);

        $billing->recordPayment($invoice, $validated);

        return back()->with('success', 'Payment recorded.');
    }

    public function void(Invoice $invoice, BillingService $billing): RedirectResponse
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return back()->with('error', 'Paid invoices cannot be voided.');
        }

        $billing->voidInvoice($invoice);

        return back()->with('success', 'Invoice voided.');
    }
}
