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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_type')->default('consultation')->after('source');
            $table->integer('duration')->default(30)->after('appointment_type');
            $table->text('notes')->nullable()->after('duration');
            $table->boolean('reminder_sent')->default(false)->after('notes');
            $table->timestamp('reminder_sent_at')->nullable()->after('reminder_sent');
            $table->string('cancellation_reason')->nullable()->after('reminder_sent_at');
            $table->string('cancelled_by')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->timestamp('check_in_time')->nullable()->after('cancelled_at');
            $table->timestamp('check_out_time')->nullable()->after('check_in_time');
            $table->integer('wait_time')->nullable()->after('check_out_time');
            $table->string('priority')->default('normal')->after('wait_time');
            $table->json('insurance_info')->nullable()->after('priority');
            $table->decimal('copay_amount', 10, 2)->nullable()->after('insurance_info');
            $table->decimal('total_amount', 10, 2)->nullable()->after('copay_amount');
            
            // Add indexes for better performance
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'doctor_id']);
            $table->index(['clinic_id', 'start_at']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'appointment_type']);
            $table->index(['clinic_id', 'priority']);
            $table->index(['clinic_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['clinic_id', 'patient_id']);
            $table->dropIndex(['clinic_id', 'doctor_id']);
            $table->dropIndex(['clinic_id', 'start_at']);
            $table->dropIndex(['clinic_id', 'status']);
            $table->dropIndex(['clinic_id', 'appointment_type']);
            $table->dropIndex(['clinic_id', 'priority']);
            $table->dropIndex(['clinic_id', 'room_id']);
            
            $table->dropColumn([
                'appointment_type',
                'duration',
                'notes',
                'reminder_sent',
                'reminder_sent_at',
                'cancellation_reason',
                'cancelled_by',
                'cancelled_at',
                'check_in_time',
                'check_out_time',
                'wait_time',
                'priority',
                'insurance_info',
                'copay_amount',
                'total_amount'
            ]);
        });
    }
};
