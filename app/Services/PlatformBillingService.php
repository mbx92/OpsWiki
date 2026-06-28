<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Collection;

class PlatformBillingService
{
    public function __construct(
        private PlatformStatsService $stats,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $base = $this->stats->summary();

        $openInvoices = Invoice::query()->whereIn('status', [Invoice::STATUS_OPEN, Invoice::STATUS_OVERDUE]);
        $paidThisMonth = Payment::query()
            ->where('status', Payment::STATUS_COMPLETED)
            ->where('paid_at', '>=', now()->startOfMonth());

        $activeYearly = Subscription::query()
            ->where('billing_interval', 'yearly')
            ->whereIn('status', ['active', 'trialing'])
            ->with('plan')
            ->get();

        return array_merge($base, [
            'arr_cents' => $this->arrCents($activeYearly),
            'outstanding_cents' => (int) (clone $openInvoices)->sum('total_cents'),
            'open_invoices_count' => (clone $openInvoices)->count(),
            'overdue_invoices_count' => Invoice::where('status', Invoice::STATUS_OVERDUE)->count(),
            'paid_this_month_cents' => (int) $paidThisMonth->sum('amount_cents'),
            'payments_this_month_count' => (clone $paidThisMonth)->count(),
            'trialing_count' => Subscription::where('status', 'trialing')->count(),
            'cancelled_count' => Subscription::where('status', 'cancelled')->count(),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentInvoices(int $limit = 10): Collection
    {
        return Invoice::query()
            ->with(['tenant:id,name,slug', 'plan:id,name'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Invoice $invoice) => $this->invoiceRow($invoice));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentPayments(int $limit = 8): Collection
    {
        return Payment::query()
            ->with(['tenant:id,name,slug', 'invoice:id,number'])
            ->where('status', Payment::STATUS_COMPLETED)
            ->latest('paid_at')
            ->limit($limit)
            ->get()
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'amount_cents' => $payment->amount_cents,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'tenant' => $payment->tenant?->only(['id', 'name', 'slug']),
                'invoice' => $payment->invoice?->only(['id', 'number']),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function subscriptionRows(int $limit = 15): Collection
    {
        return Subscription::query()
            ->with(['tenant:id,name,slug,status', 'plan:id,name,slug,price_monthly_cents,currency'])
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Subscription $sub) => [
                'id' => $sub->id,
                'status' => $sub->status,
                'billing_interval' => $sub->billing_interval,
                'current_period_start' => $sub->current_period_start?->toIso8601String(),
                'current_period_end' => $sub->current_period_end?->toIso8601String(),
                'trial_ends_at' => $sub->trial_ends_at?->toIso8601String(),
                'tenant' => $sub->tenant?->only(['id', 'name', 'slug', 'status']),
                'plan' => $sub->plan?->only(['id', 'name', 'slug', 'price_monthly_cents', 'currency']),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function invoiceRow(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'total_cents' => $invoice->total_cents,
            'paid_cents' => $invoice->paidCents(),
            'balance_cents' => $invoice->balanceCents(),
            'billing_interval' => $invoice->billing_interval,
            'due_at' => $invoice->due_at?->toIso8601String(),
            'paid_at' => $invoice->paid_at?->toIso8601String(),
            'created_at' => $invoice->created_at?->toIso8601String(),
            'tenant' => $invoice->tenant?->only(['id', 'name', 'slug']),
            'plan' => $invoice->plan?->only(['id', 'name']),
        ];
    }

    /**
     * @param  Collection<int, Subscription>  $yearlySubscriptions
     */
    private function arrCents(Collection $yearlySubscriptions): int
    {
        $monthlyMrr = $this->stats->summary()['mrr_cents'];

        $yearlyContribution = (int) $yearlySubscriptions->sum(
            fn (Subscription $sub) => $sub->plan?->price_yearly_cents ?? 0,
        );

        return ($monthlyMrr * 12) + $yearlyContribution;
    }
}
