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
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('capacity')->default(1)->after('type');
            $table->string('status')->default('Available')->after('capacity');
            $table->text('location')->nullable()->after('status');
            $table->text('description')->nullable()->after('location');
            $table->json('equipment')->nullable()->after('description');
            $table->text('maintenance_notes')->nullable()->after('equipment');
            $table->text('special_requirements')->nullable()->after('maintenance_notes');
            $table->boolean('is_active')->default(true)->after('special_requirements');
            $table->integer('floor_number')->nullable()->after('is_active');
            $table->string('wing')->nullable()->after('floor_number');
            $table->json('accessibility_features')->nullable()->after('wing');
            $table->string('cleaning_schedule')->nullable()->after('accessibility_features');
            $table->timestamp('last_maintenance_date')->nullable()->after('cleaning_schedule');
            $table->timestamp('next_maintenance_date')->nullable()->after('last_maintenance_date');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'capacity',
                'status',
                'location',
                'description',
                'equipment',
                'maintenance_notes',
                'special_requirements',
                'is_active',
                'floor_number',
                'wing',
                'accessibility_features',
                'cleaning_schedule',
                'last_maintenance_date',
                'next_maintenance_date',
                'deleted_at'
            ]);
        });
    }
};
