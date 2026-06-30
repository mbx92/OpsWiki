<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantContentRepairService
{
    /** @var list<string> */
    public const CONTENT_TABLES = [
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
        'page_relations',
    ];

    /**
     * @return array{tenant_id: int, backfilled: array<string, int>, users_linked: int}
     */
    public function repair(): array
    {
        $tenant = $this->ensureDefaultTenant();
        $backfilled = [];

        foreach (self::CONTENT_TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $backfilled[$table] = DB::table($table)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenant->id]);
        }

        $usersLinked = $this->linkAllUsersToTenant($tenant->id);

        return [
            'tenant_id' => $tenant->id,
            'backfilled' => $backfilled,
            'users_linked' => $usersLinked,
        ];
    }

    public function ensureDefaultTenant(): Tenant
    {
        $slug = (string) config('saas.default_tenant_slug', 'default');

        return Tenant::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => 'Default Workspace', 'status' => 'active'],
        );
    }

    private function linkAllUsersToTenant(int $tenantId): int
    {
        if (! Schema::hasTable('tenant_user')) {
            return 0;
        }

        $linked = 0;
        $now = now();

        foreach (DB::table('users')->pluck('id') as $userId) {
            $inserted = DB::table('tenant_user')->insertOrIgnore([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'role' => 'member',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($inserted === 1) {
                $linked++;
            }
        }

        return $linked;
    }
}
