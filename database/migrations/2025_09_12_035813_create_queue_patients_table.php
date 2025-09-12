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
        Schema::create('queue_patients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('queue_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->integer('priority')->default(1); // 1-5 scale
            $table->enum('status', ['waiting', 'called', 'served', 'removed', 'no_show'])->default('waiting');
            $table->timestamp('joined_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->integer('estimated_wait_time')->default(0); // in minutes
            $table->integer('actual_wait_time')->default(0); // in minutes
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['queue_id', 'status']);
            $table->index(['queue_id', 'priority']);
            $table->index(['patient_id', 'status']);
            $table->index(['status', 'joined_at']);
            $table->index('joined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_patients');
    }
};