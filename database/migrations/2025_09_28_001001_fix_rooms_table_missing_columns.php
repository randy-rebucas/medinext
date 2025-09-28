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
            // Add missing columns that should have been added by the previous migration
            if (!Schema::hasColumn('rooms', 'capacity')) {
                $table->integer('capacity')->default(1)->after('type');
            }
            if (!Schema::hasColumn('rooms', 'status')) {
                $table->string('status')->default('Available')->after('capacity');
            }
            if (!Schema::hasColumn('rooms', 'location')) {
                $table->text('location')->nullable()->after('status');
            }
            if (!Schema::hasColumn('rooms', 'description')) {
                $table->text('description')->nullable()->after('location');
            }
            if (!Schema::hasColumn('rooms', 'equipment')) {
                $table->json('equipment')->nullable()->after('description');
            }
            if (!Schema::hasColumn('rooms', 'maintenance_notes')) {
                $table->text('maintenance_notes')->nullable()->after('equipment');
            }
            if (!Schema::hasColumn('rooms', 'special_requirements')) {
                $table->text('special_requirements')->nullable()->after('maintenance_notes');
            }
            if (!Schema::hasColumn('rooms', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('special_requirements');
            }
            if (!Schema::hasColumn('rooms', 'floor_number')) {
                $table->integer('floor_number')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('rooms', 'wing')) {
                $table->string('wing')->nullable()->after('floor_number');
            }
            if (!Schema::hasColumn('rooms', 'accessibility_features')) {
                $table->json('accessibility_features')->nullable()->after('wing');
            }
            if (!Schema::hasColumn('rooms', 'cleaning_schedule')) {
                $table->string('cleaning_schedule')->nullable()->after('accessibility_features');
            }
            if (!Schema::hasColumn('rooms', 'last_maintenance_date')) {
                $table->timestamp('last_maintenance_date')->nullable()->after('cleaning_schedule');
            }
            if (!Schema::hasColumn('rooms', 'next_maintenance_date')) {
                $table->timestamp('next_maintenance_date')->nullable()->after('last_maintenance_date');
            }
            if (!Schema::hasColumn('rooms', 'deleted_at')) {
                $table->softDeletes();
            }
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
