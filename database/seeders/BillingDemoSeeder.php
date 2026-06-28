<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\BillingService;
use Illuminate\Database\Seeder;

class BillingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $billing = app(BillingService::class);

        Subscription::query()->each(function (Subscription $subscription) use ($billing) {
            $billing->ensureSubscriptionPeriod($subscription);

            if ($subscription->plan?->slug === 'free') {
                return;
            }

            $tenant = $subscription->tenant;
            if (! $tenant) {
                return;
            }

            $paidInvoice = $billing->createInvoice($tenant, [
                'subscription_id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'billing_interval' => $subscription->billing_interval ?? 'monthly',
                'period_start' => now()->subMonth()->startOfMonth(),
                'period_end' => now()->subMonth()->endOfMonth(),
                'due_at' => now()->subMonth()->addDays(7),
            ]);

            $billing->recordPayment($paidInvoice, [
                'amount_cents' => $paidInvoice->total_cents,
                'method' => Payment::METHOD_BANK_TRANSFER,
                'reference' => 'DEMO-'.strtoupper(substr(md5((string) $paidInvoice->id), 0, 8)),
                'paid_at' => now()->subMonth()->addDays(3),
            ]);

            $billing->createInvoice($tenant, [
                'subscription_id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'billing_interval' => $subscription->billing_interval ?? 'monthly',
                'status' => Invoice::STATUS_OPEN,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'due_at' => now()->addDays(14),
            ]);
        });

        $freeTenant = Tenant::where('slug', config('saas.default_tenant_slug', 'default'))->first();
        $teamPlan = Plan::where('slug', 'team')->first();

        if ($freeTenant && $teamPlan && ! Invoice::where('tenant_id', $freeTenant->id)->exists()) {
            $billing->createInvoice($freeTenant, [
                'plan_id' => $teamPlan->id,
                'billing_interval' => 'monthly',
                'status' => Invoice::STATUS_OVERDUE,
                'due_at' => now()->subDays(5),
            ]);
        }
    }
}
