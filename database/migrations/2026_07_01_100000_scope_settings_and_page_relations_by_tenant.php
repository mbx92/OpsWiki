<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultTenantId = DB::table('tenants')
            ->where('slug', config('saas.default_tenant_slug', 'default'))
            ->value('id');

        if ($defaultTenantId) {
            DB::table('settings')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['tenant_id', 'key']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE settings DROP CONSTRAINT IF EXISTS settings_pkey');
            DB::statement('ALTER TABLE settings ALTER COLUMN tenant_id SET NOT NULL');
            DB::statement('ALTER TABLE settings ADD PRIMARY KEY (tenant_id, key)');
        }

        if (! Schema::hasColumn('page_relations', 'tenant_id')) {
            Schema::table('page_relations', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if ($defaultTenantId) {
            DB::table('page_relations')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('page_relations', 'tenant_id')) {
            Schema::table('page_relations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE settings DROP CONSTRAINT IF EXISTS settings_pkey');
            DB::statement('ALTER TABLE settings ADD PRIMARY KEY (key)');
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'key']);
        });
    }
};
