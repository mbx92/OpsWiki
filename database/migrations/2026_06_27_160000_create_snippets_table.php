<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snippets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('command');
            $table->string('language')->default('bash');
            $table->string('platform')->nullable(); // linux, macos, windows, docker, proxmox, postgresql, minio, mikrotik, synology, qnap, cloudflare, tailscale
            $table->json('variables')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_tested')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('platform');
            $table->index('is_favorite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snippets');
    }
};
