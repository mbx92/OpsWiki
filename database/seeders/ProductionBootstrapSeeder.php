<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Idempotent bootstrap for production deploys.
 * Safe to run on every deployment — does not truncate or reset tables.
 */
class ProductionBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            SaasFoundationSeeder::class,
        ]);
    }
}
