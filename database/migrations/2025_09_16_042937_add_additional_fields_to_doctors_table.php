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
        Schema::table('doctors', function (Blueprint $table) {
            $table->text('experience')->nullable()->after('license_no');
            $table->text('education')->nullable()->after('experience');
            $table->text('certifications')->nullable()->after('education');
            $table->text('address')->nullable()->after('certifications');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('emergency_phone')->nullable()->after('emergency_contact');
            $table->text('notes')->nullable()->after('emergency_phone');
            $table->json('availability')->nullable()->after('availability_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'experience',
                'education', 
                'certifications',
                'address',
                'emergency_contact',
                'emergency_phone',
                'notes',
                'availability'
            ]);
        });
    }
};
