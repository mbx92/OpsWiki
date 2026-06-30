<?php

use App\Services\TenantContentRepairService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(TenantContentRepairService::class)->repair();
    }

    public function down(): void
    {
        // Data repair is not reversed.
    }
};
