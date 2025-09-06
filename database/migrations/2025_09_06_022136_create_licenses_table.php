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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // License Information
            $table->string('license_key')->unique();
            $table->string('license_type')->default('standard'); // standard, premium, enterprise
            $table->string('status')->default('active'); // active, expired, suspended, revoked
            $table->string('name');
            $table->text('description')->nullable();

            // Customer Information
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_company')->nullable();
            $table->string('customer_phone')->nullable();

            // License Limits
            $table->integer('max_users')->default(10);
            $table->integer('max_clinics')->default(1);
            $table->integer('max_patients')->default(1000);
            $table->integer('max_appointments_per_month')->default(1000);
            $table->json('features')->nullable(); // Array of enabled features

            // Validity Period
            $table->date('starts_at');
            $table->date('expires_at');
            $table->integer('grace_period_days')->default(7); // Grace period after expiration

            // Server Information
            $table->string('server_domain')->nullable();
            $table->string('server_ip')->nullable();
            $table->string('server_fingerprint')->nullable(); // Server identification

            // License Management
            $table->boolean('auto_renew')->default(false);
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly, lifetime
            $table->date('last_payment_date')->nullable();
            $table->date('next_payment_date')->nullable();

            // Usage Tracking
            $table->integer('current_users')->default(0);
            $table->integer('current_clinics')->default(0);
            $table->integer('current_patients')->default(0);
            $table->integer('appointments_this_month')->default(0);
            $table->date('last_usage_reset')->nullable();

            // Security
            $table->string('activation_code')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->integer('validation_attempts')->default(0);
            $table->timestamp('last_validation_attempt')->nullable();

            // Support Information
            $table->string('support_level')->default('standard'); // standard, premium, enterprise
            $table->text('support_notes')->nullable();
            $table->string('assigned_support_agent')->nullable();

            // Audit Trail
            $table->json('audit_log')->nullable(); // Track license changes
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['license_key', 'status']);
            $table->index(['customer_email']);
            $table->index(['expires_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
