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
        Schema::create('emrs', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');

            // Clinical data
            $table->string('visit_type')->nullable(); // consultation, follow-up, emergency
            $table->string('chief_complaint')->nullable();
            $table->json('vitals')->nullable(); // { bp: "120/80", temp: "37.1", hr: 80 }
            $table->text('history')->nullable(); // HPI, past medical history
            $table->text('exam_findings')->nullable(); // physical exam notes
            $table->text('diagnosis')->nullable(); // main diagnosis
            $table->text('treatment_plan')->nullable(); // procedures, lifestyle advice
            $table->json('labs')->nullable(); // lab test results (JSON)
            $table->json('attachments')->nullable(); // file URLs (X-ray, scans, etc.)

            // Status
            $table->string('status')->default('open'); // open, closed, referred
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emrs');
    }
};
