<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('purpose')->nullable();
            $table->text('use_case')->nullable();
            $table->text('requirements')->nullable();
            $table->json('variables')->nullable();
            $table->longText('steps')->nullable();
            $table->text('validation')->nullable();
            $table->text('rollback')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft'); // draft, review, tested, production, deprecated, archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
