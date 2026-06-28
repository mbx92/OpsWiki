<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "INV-{$year}-";

        $last = Invoice::query()
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('number')
            ->value('number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createInvoice(Tenant $tenant, array $data): Invoice
    {
        return DB::transaction(function () use ($tenant, $data) {
            $subscription = isset($data['subscription_id'])
                ? Subscription::query()->where('tenant_id', $tenant->id)->findOrFail($data['subscription_id'])
                : $tenant->subscription;

            $plan = isset($data['plan_id'])
                ? Plan::findOrFail($data['plan_id'])
                : ($subscription?->plan ?? $tenant->currentPlan());

            $billingInterval = $data['billing_interval'] ?? $subscription?->billing_interval ?? 'monthly';
            $amountCents = (int) ($data['total_cents'] ?? $this->amountForPlan($plan, $billingInterval));

            $lineItems = $data['line_items'] ?? [[
                'description' => ($plan?->name ?? 'Subscription').' — '.ucfirst($billingInterval),
                'amount_cents' => $amountCents,
            ]];

            $subtotal = (int) collect($lineItems)->sum('amount_cents');
            $taxCents = (int) ($data['tax_cents'] ?? 0);
            $totalCents = (int) ($data['total_cents'] ?? ($subtotal + $taxCents));

            $periodStart = $data['period_start'] ?? $subscription?->current_period_start ?? now()->startOfMonth();
            $periodEnd = $data['period_end'] ?? $subscription?->current_period_end ?? now()->endOfMonth();

            return Invoice::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription?->id,
                'plan_id' => $plan?->id,
                'number' => $this->generateInvoiceNumber(),
                'status' => $data['status'] ?? Invoice::STATUS_OPEN,
                'currency' => $data['currency'] ?? $plan?->currency ?? 'USD',
                'subtotal_cents' => $subtotal,
                'tax_cents' => $taxCents,
                'total_cents' => $totalCents,
                'billing_interval' => $billingInterval,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'due_at' => $data['due_at'] ?? now()->addDays(14),
                'notes' => $data['notes'] ?? null,
                'line_items' => $lineItems,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'recorded_by_user_id' => $data['recorded_by_user_id'] ?? auth()->id(),
                'amount_cents' => (int) $data['amount_cents'],
                'currency' => $data['currency'] ?? $invoice->currency,
                'method' => $data['method'] ?? Payment::METHOD_MANUAL,
                'status' => $data['status'] ?? Payment::STATUS_COMPLETED,
                'reference' => $data['reference'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncInvoiceStatus($invoice->fresh(['payments']));

            return $payment;
        });
    }

    public function syncInvoiceStatus(Invoice $invoice): Invoice
    {
        if ($invoice->status === Invoice::STATUS_VOID) {
            return $invoice;
        }

        $paidCents = $invoice->paidCents();

        if ($paidCents >= $invoice->total_cents && $invoice->total_cents > 0) {
            $invoice->update([
                'status' => Invoice::STATUS_PAID,
                'paid_at' => $invoice->paid_at ?? now(),
            ]);

            return $invoice->fresh();
        }

        if ($invoice->due_at && $invoice->due_at->isPast() && $paidCents < $invoice->total_cents) {
            $invoice->update(['status' => Invoice::STATUS_OVERDUE]);

            return $invoice->fresh();
        }

        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            $invoice->update(['status' => Invoice::STATUS_OPEN]);
        }

        return $invoice->fresh();
    }

    public function voidInvoice(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status' => Invoice::STATUS_VOID,
            'paid_at' => null,
        ]);

        return $invoice->fresh();
    }

    public function ensureSubscriptionPeriod(Subscription $subscription): Subscription
    {
        if ($subscription->current_period_start && $subscription->current_period_end) {
            return $subscription;
        }

        $start = now()->startOfMonth();
        $end = $subscription->billing_interval === 'yearly'
            ? now()->addYear()->endOfMonth()
            : now()->endOfMonth();

        $subscription->update([
            'current_period_start' => $start,
            'current_period_end' => $end,
        ]);

        return $subscription->fresh();
    }

    private function amountForPlan(?Plan $plan, string $interval): int
    {
        if (! $plan) {
            return 0;
        }

        return $interval === 'yearly'
            ? (int) $plan->price_yearly_cents
            : (int) $plan->price_monthly_cents;
    }
}
