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
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('encounter_id')->nullable()->constrained()->onDelete('set null');
            $table->string('test_type');
            $table->string('test_name');
            $table->text('result_value');
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->enum('status', ['pending', 'completed', 'abnormal', 'critical'])->default('pending');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('ordered_by_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->foreignId('reviewed_by_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'test_type']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'ordered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
