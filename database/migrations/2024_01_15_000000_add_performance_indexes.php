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
        // Add performance indexes for common queries

        // Users table indexes - already exist, skipping

        // Patients table indexes - already exist, skipping

        // Appointments table indexes - already exist, skipping

        // Encounters table indexes - already exist, skipping

        // Prescriptions table indexes - already exist, skipping

        // Bills table indexes - already exist, skipping

        // Activity logs table indexes - already exist, skipping

        // Settings table indexes - already exist, skipping

        // Licenses table indexes - already exist, skipping

        // User clinic roles table indexes - already exist, skipping

        // File assets table indexes - already exist, skipping

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['type', 'created_at'], 'idx_notifications_type_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop performance indexes

        // Users table indexes - already exist, skipping

        // Patients table indexes - already exist, skipping

        // Appointments table indexes - already exist, skipping

        // Encounters table indexes - already exist, skipping

        // Prescriptions table indexes - already exist, skipping

        // Bills table indexes - already exist, skipping

        // Activity logs table indexes - already exist, skipping

        // Settings table indexes - already exist, skipping

        // Licenses table indexes - already exist, skipping

        // User clinic roles table indexes - already exist, skipping

        // File assets table indexes - already exist, skipping

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_type_created');
        });
    }
};
