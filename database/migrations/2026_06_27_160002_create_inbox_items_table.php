<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('type')->default('idea'); // idea, error_log, command, link, draft_sop, draft_documentation, temporary_note
            $table->string('source')->nullable(); // where the item came from
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->json('tags')->nullable();
            $table->string('status')->default('new'); // new, reviewed, converted, archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_items');
    }
};
