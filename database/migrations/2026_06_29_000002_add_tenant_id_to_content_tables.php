<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
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
    ];

    /** @var list<string> */
    private array $slugTables = [
        'pages',
        'books',
        'categories',
        'tags',
        'sops',
        'troubleshooting_cases',
        'projects',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('key')->constrained()->cascadeOnDelete();
        });

        foreach ($this->slugTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->unique(['tenant_id', 'slug']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->slugTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'slug']);
                $table->unique(['slug']);
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
