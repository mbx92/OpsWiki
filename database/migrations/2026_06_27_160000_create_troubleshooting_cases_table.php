<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('troubleshooting_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('symptoms')->nullable();
            $table->text('environment')->nullable();
            $table->longText('error_log')->nullable();
            $table->text('suspected_causes')->nullable();
            $table->longText('diagnosis_steps')->nullable();
            $table->longText('working_solution')->nullable();
            $table->longText('failed_attempts')->nullable();
            $table->text('validation')->nullable();
            $table->text('prevention')->nullable();
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('open'); // open, investigating, solved, workaround, failed, archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('severity');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('troubleshooting_cases');
    }
};
