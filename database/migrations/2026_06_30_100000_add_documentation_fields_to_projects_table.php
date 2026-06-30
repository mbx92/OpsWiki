<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('deployment_notes')->nullable()->after('environment_notes');
            $table->text('database_notes')->nullable()->after('deployment_notes');
            $table->text('backup_notes')->nullable()->after('database_notes');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['deployment_notes', 'database_notes', 'backup_notes']);
        });
    }
};
