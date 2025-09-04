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
        Schema::table('encounters', function (Blueprint $table) {
            $table->string('encounter_number')->unique()->after('id');
            $table->string('chief_complaint')->nullable()->after('encounter_number');
            $table->text('assessment')->nullable()->after('chief_complaint');
            $table->text('plan')->nullable()->after('assessment');
            $table->timestamp('follow_up_date')->nullable()->after('plan');
            $table->string('visit_type')->nullable()->after('follow_up_date');
            $table->string('payment_status')->default('pending')->after('visit_type');
            $table->decimal('billing_amount', 10, 2)->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            $table->dropColumn([
                'encounter_number',
                'chief_complaint',
                'assessment',
                'plan',
                'follow_up_date',
                'visit_type',
                'payment_status',
                'billing_amount'
            ]);
        });
    }
};
