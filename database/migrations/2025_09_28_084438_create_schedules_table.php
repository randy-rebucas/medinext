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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['Active', 'Inactive', 'On Leave', 'Vacation', 'Sick Leave'])->default('Active');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_type', ['none', 'weekly', 'biweekly', 'monthly'])->default('none');
            $table->integer('recurring_interval')->default(1);
            $table->date('recurring_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->integer('max_appointments')->default(10);
            $table->integer('appointment_duration')->default(30); // in minutes
            $table->integer('break_duration')->default(0); // in minutes
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['clinic_id', 'doctor_id']);
            $table->index(['clinic_id', 'day_of_week']);
            $table->index(['doctor_id', 'day_of_week']);
            $table->index(['status', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
