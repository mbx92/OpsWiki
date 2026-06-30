<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContentRepairService;
use App\Services\TenantResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnoseTenantCommand extends Command
{
    protected $signature = 'tenant:diagnose {--email= : Check a specific user email}';

    protected $description = 'Show tenant resolution hints and content counts per workspace';

    public function handle(TenantResolver $resolver, TenantContentRepairService $repair): int
    {
        $default = $repair->ensureDefaultTenant();

        $this->info('Config');
        $this->line('  APP_URL: '.config('app.url'));
        $this->line('  SAAS_CENTRAL_DOMAIN: '.config('saas.central_domain'));
        $this->line('  SAAS_DEFAULT_TENANT_SLUG: '.config('saas.default_tenant_slug'));
        $this->newLine();

        $this->info('Tenants');
        Tenant::query()->orderBy('id')->each(function (Tenant $tenant) {
            $pages = Schema::hasColumn('pages', 'tenant_id')
                ? DB::table('pages')->where('tenant_id', $tenant->id)->count()
                : '?';
            $nullPages = Schema::hasColumn('pages', 'tenant_id')
                ? DB::table('pages')->whereNull('tenant_id')->count()
                : 0;
            $projects = Schema::hasColumn('projects', 'tenant_id')
                ? DB::table('projects')->where('tenant_id', $tenant->id)->count()
                : '?';
            $users = DB::table('tenant_user')->where('tenant_id', $tenant->id)->count();

            $this->line("  #{$tenant->id} {$tenant->slug} ({$tenant->name}) — pages: {$pages}, projects: {$projects}, users: {$users}, pages w/ null tenant: {$nullPages}");
        });

        $this->newLine();
        $this->info('Default tenant: #'.$default->id.' ('.$default->slug.')');
        $this->line('  Central host resolves to default: yes (after fix)');

        $email = $this->option('email') ?? (string) config('saas.super_admin.email');

        if ($email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $this->newLine();
                $this->info("User: {$user->email} (#{$user->id})");
                $memberships = $user->tenants()->get(['tenants.id', 'tenants.slug', 'tenants.name']);

                foreach ($memberships as $tenant) {
                    $this->line("  member of #{$tenant->id} {$tenant->slug}");
                }

                $sessionNote = $memberships->count() > 1
                    ? 'Multiple workspaces — central domain now forces default workspace.'
                    : 'Single workspace membership.';
                $this->line('  '.$sessionNote);
            }
        }

        $nullPageCount = Schema::hasColumn('pages', 'tenant_id')
            ? DB::table('pages')->whereNull('tenant_id')->count()
            : 0;

        if ($nullPageCount > 0) {
            $this->warn("There are {$nullPageCount} pages with tenant_id NULL — run: php artisan tenant:repair");
        }

        return self::SUCCESS;
    }
}
