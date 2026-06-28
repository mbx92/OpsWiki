<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PlanMarketingCatalog;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaasFoundationSeeder extends Seeder
{
    /** @var list<string> */
    private array $tenantTables = [
        'pages',
        'books',
        'categories',
        'tags',
        'sops',
        'troubleshooting_cases',
        'snippets',
        'projects',
        'inbox_items',
        'assets',
        'activity_logs',
        'settings',
    ];

    public function run(): void
    {
        $this->seedPlans();
        $this->seedLegalDocuments();

        $tenant = Tenant::updateOrCreate(
            ['slug' => config('saas.default_tenant_slug', 'default')],
            ['name' => 'Default Workspace', 'status' => 'active'],
        );

        $teamPlan = Plan::where('slug', 'team')->first()
            ?? Plan::where('slug', 'free')->first();
        if ($teamPlan) {
            Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'plan_id' => $teamPlan->id,
                    'status' => 'active',
                    'billing_interval' => 'monthly',
                    'current_period_start' => now()->startOfMonth(),
                    'current_period_end' => now()->endOfMonth(),
                    'billing_email' => config('saas.super_admin.email'),
                ],
            );
        }

        $ownerRole = Role::where('slug', 'owner')->first();
        $adminEmail = (string) config('saas.super_admin.email');
        $admin = User::where('email', $adminEmail)->first();

        if ($admin) {
            $admin->update([
                'is_super_admin' => true,
                'role_id' => $ownerRole?->id ?? $admin->role_id,
            ]);
            $tenant->users()->syncWithoutDetaching([$admin->id => ['role' => 'owner']]);
        }

        User::query()
            ->where('email', '!=', $adminEmail)
            ->each(function (User $user) use ($tenant) {
                $tenant->users()->syncWithoutDetaching([$user->id => ['role' => 'member']]);
            });

        TenantContext::set($tenant);
        $this->backfillTenantIds($tenant->id);
        TenantContext::set(null);

        $this->call(BillingDemoSeeder::class);
    }

    private function seedPlans(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'description' => 'For individuals and small teams getting started with core wiki features.',
                'price_monthly_cents' => 0,
                'price_yearly_cents' => 0,
                'currency' => 'USD',
                'features' => PlanMarketingCatalog::defaultMarketingFeatures('free'),
                'limits' => config('saas.default_limits.free'),
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'For growing teams — books, SOPs, tools, integrations, and unlimited content.',
                'price_monthly_cents' => 1900,
                'price_yearly_cents' => 19000,
                'currency' => 'USD',
                'features' => PlanMarketingCatalog::defaultMarketingFeatures('pro'),
                'limits' => config('saas.default_limits.pro'),
                'is_popular' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'team',
                'name' => 'Team',
                'description' => 'For organizations that need audit logs and custom role permissions.',
                'price_monthly_cents' => 4900,
                'price_yearly_cents' => 49000,
                'currency' => 'USD',
                'features' => PlanMarketingCatalog::defaultMarketingFeatures('team'),
                'limits' => config('saas.default_limits.team'),
                'is_popular' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], [
                ...$plan,
                'gates' => PlanMarketingCatalog::enabledGateKeysForPlan($plan['slug']),
            ]);
        }
    }

    private function seedLegalDocuments(): void
    {
        $documents = [
            'terms' => [
                'title' => 'Terms of Service',
                'content' => <<<'MD'
## Acceptance

By using OpsWiki, you agree to these terms.

## Service

OpsWiki provides hosted wiki and documentation workspace for technical teams.

## Your content

You retain ownership of content you upload. You grant us license to host and display it as needed to operate the service.

## Acceptable use

Do not use the service for illegal activity, malware distribution, or abuse of shared infrastructure.

## Changes

We may update these terms. Continued use after changes constitutes acceptance.
MD,
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'content' => <<<'MD'
## Data we collect

Account information (name, email), workspace content you create, and usage logs for security and reliability.

## How we use data

To provide the service, improve reliability, and communicate about your account.

## Data storage

Content is stored in your selected region/infrastructure. Backups may be retained per our retention policy.

## Contact

For privacy requests, contact your workspace administrator or platform operator.
MD,
            ],
            'acceptable-use' => [
                'title' => 'Acceptable Use Policy',
                'content' => <<<'MD'
## Permitted use

Technical documentation, SOPs, internal knowledge bases, and related team content.

## Prohibited use

- Spam or unsolicited bulk messaging
- Hosting public file sharing unrelated to documentation
- Attempting to bypass security or access other tenants' data
- Mining cryptocurrency or running unrelated compute workloads
MD,
            ],
        ];

        foreach ($documents as $slug => $doc) {
            LegalDocument::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $doc['title'],
                    'content_markdown' => trim($doc['content']),
                    'status' => 'published',
                    'version' => 1,
                ],
            );
        }
    }

    private function backfillTenantIds(int $tenantId): void
    {
        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }
    }
}
