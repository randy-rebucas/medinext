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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('patient_id')->nullable()->after('code');
            $table->json('emergency_contact')->nullable()->after('contact');
            $table->json('insurance')->nullable()->after('emergency_contact');
            $table->text('medical_history')->nullable()->after('allergies');
            $table->text('medications')->nullable()->after('medical_history');
            $table->text('notes')->nullable()->after('medications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'patient_id',
                'emergency_contact',
                'insurance',
                'medical_history',
                'medications',
                'notes'
            ]);
        });
    }
};
