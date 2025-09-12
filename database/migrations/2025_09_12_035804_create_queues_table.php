<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('queue_type', ['general', 'emergency', 'appointment', 'walk_in', 'consultation', 'lab', 'pharmacy'])->default('general');
            $table->enum('status', ['active', 'paused', 'closed', 'maintenance'])->default('active');
            $table->integer('max_capacity')->default(50);
            $table->integer('current_count')->default(0);
            $table->integer('average_wait_time')->default(0); // in minutes
            $table->integer('estimated_wait_time')->default(0); // in minutes
            $table->integer('priority_level')->default(1); // 1-5 scale
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_assign')->default(false);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
            $table->index(['clinic_id', 'status']);
            $table->index(['queue_type', 'is_active']);
            $table->index('priority_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};