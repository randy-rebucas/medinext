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
        // Core Laravel tables
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('license_key')->nullable();
            $table->boolean('is_trial_user')->default(true);
            $table->boolean('has_activated_license')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache tables
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Job tables
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Personal access tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // Roles and permissions
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // admin, doctor, receptionist, patient, medrep, superadmin
            $table->text('description')->nullable();
            $table->boolean('is_system_role')->default(false);
            $table->json('permissions_config')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('module')->index(); // clinics, doctors, patients, etc.
            $table->string('action')->index(); // view, create, edit, delete, manage
            $table->timestamps();

            $table->index(['module', 'action']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        // Clinics
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('slug')->unique()->nullable();
            $table->string('timezone')->default('Asia/Manila');
            $table->text('logo_url')->nullable();
            $table->json('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // User clinic roles
        Schema::create('user_clinic_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('role_id');
            $table->string('department')->nullable();
            $table->string('status')->default('Active');
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->text('notes')->nullable();
            $table->date('join_date')->nullable();
            $table->unique(['user_id', 'clinic_id', 'role_id']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        // Doctors
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->text('specialty')->nullable();
            $table->text('license_no')->nullable();
            $table->text('signature_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->json('availability_schedule')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->index(['clinic_id', 'user_id']);
        });

        // Patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->text('code')->nullable();
            $table->string('first_name', 255)->index();
            $table->string('last_name', 255)->index();
            $table->date('dob')->nullable()->index();
            $table->text('sex')->nullable();
            $table->json('contact')->nullable();
            $table->json('allergies')->nullable();
            $table->json('consents')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->index(['first_name', 'last_name']);
        });

        // Rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->text('name');
            $table->text('type')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });

        // Encounters
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_number')->unique()->nullable();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->timestamp('date')->useCurrent()->index();
            $table->text('type')->nullable();
            $table->text('status')->nullable();
            $table->text('notes_soap')->nullable();
            $table->json('vitals')->nullable();
            $table->text('diagnosis_codes')->nullable();
            $table->string('chief_complaint')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->timestamp('follow_up_date')->nullable();
            $table->string('visit_type')->nullable();
            $table->string('payment_status')->default('pending');
            $table->decimal('billing_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'doctor_id']);
        });

        // File assets
        Schema::create('file_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->string('owner_type', 100)->index(); // patient, doctor, prescription, etc.
            $table->unsignedBigInteger('owner_id')->index();
            $table->string('url', 500);
            $table->string('mime', 100)->nullable()->index();
            $table->bigInteger('size')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('file_name')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->index(['owner_type', 'owner_id']);
        });

        // Appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('end_at')->nullable()->index();
            $table->string('status', 50)->default('booked')->index(); // booked, arrived, in-room, completed, no-show, canceled
            $table->unsignedBigInteger('room_id')->nullable()->index();
            $table->string('reason', 500)->nullable();
            $table->string('source', 50)->nullable(); // patient, receptionist, system
            $table->string('appointment_type')->default('consultation');
            $table->integer('duration')->default(30);
            $table->text('notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->integer('wait_time')->nullable();
            $table->string('priority')->default('normal');
            $table->json('insurance_info')->nullable();
            $table->decimal('copay_amount', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'doctor_id']);
            $table->index(['clinic_id', 'start_at']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'appointment_type']);
            $table->index(['clinic_id', 'priority']);
            $table->index(['clinic_id', 'room_id']);
            $table->index(['patient_id', 'start_at']);
        });

        // Prescriptions
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->unsignedBigInteger('encounter_id')->nullable()->index();
            $table->timestamp('issued_at')->useCurrent()->index();
            $table->string('status', 50)->default('active')->index(); // active, revoked
            $table->text('pdf_url')->nullable();
            $table->string('qr_hash')->unique()->nullable();
            $table->string('prescription_number')->unique()->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('encounter_id')->references('id')->on('encounters')->onDelete('set null');
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'issued_at']);
        });

        // Prescription items
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id')->index();
            $table->text('drug_name');
            $table->text('strength')->nullable();
            $table->text('form')->nullable();
            $table->text('sig')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('refills')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade');
        });

        // Activity logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('entity', 100)->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->string('action', 100)->index();
            $table->timestamp('at')->useCurrent()->index();
            $table->string('ip', 45)->nullable(); // IPv6 can be up to 45 characters
            $table->json('meta')->nullable();
            $table->string('before_hash', 64)->nullable();
            $table->string('after_hash', 64)->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('actor_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['entity', 'entity_id']);
            $table->index(['clinic_id', 'at']);
        });

        // Medreps
        Schema::create('medreps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('company')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Medrep visits
        Schema::create('medrep_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->index();
            $table->unsignedBigInteger('medrep_id')->index();
            $table->unsignedBigInteger('doctor_id')->index();
            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('end_at')->nullable()->index();
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('medrep_id')->references('id')->on('medreps')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->index(['clinic_id', 'start_at']);
            $table->index(['medrep_id', 'start_at']);
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable()->index();
            $table->string('key')->index();
            $table->json('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json, array
            $table->string('group')->default('general')->index(); // general, clinic, notifications, branding, working_hours
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['clinic_id', 'key']);
            $table->index(['clinic_id', 'group']);
        });

        // EMRs
        Schema::create('emrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable()->index();
            $table->string('visit_type')->nullable()->index(); // consultation, follow-up, emergency
            $table->string('chief_complaint')->nullable();
            $table->json('vitals')->nullable(); // { bp: "120/80", temp: "37.1", hr: 80 }
            $table->text('history')->nullable(); // HPI, past medical history
            $table->text('exam_findings')->nullable(); // physical exam notes
            $table->text('diagnosis')->nullable(); // main diagnosis
            $table->text('treatment_plan')->nullable(); // procedures, lifestyle advice
            $table->json('labs')->nullable(); // lab test results (JSON)
            $table->json('attachments')->nullable(); // file URLs (X-ray, scans, etc.)
            $table->string('status')->default('open')->index(); // open, closed, referred
            $table->timestamps();

            $table->foreign('patient_id', 'emrs_patient_id_foreign')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id', 'emrs_doctor_id_foreign')->references('id')->on('users')->onDelete('set null');
            $table->foreign('appointment_id', 'emrs_appointment_id_foreign')->references('id')->on('appointments')->onDelete('set null');
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
        });

        // Lab results
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('encounter_id')->nullable();
            $table->string('test_type');
            $table->string('test_name');
            $table->text('result_value');
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->enum('status', ['pending', 'completed', 'abnormal', 'critical'])->default('pending');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('ordered_by_doctor_id')->nullable();
            $table->unsignedBigInteger('reviewed_by_doctor_id')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id', 'lab_results_clinic_id_foreign')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('patient_id', 'lab_results_patient_id_foreign')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('encounter_id', 'lab_results_encounter_id_foreign')->references('id')->on('encounters')->onDelete('set null');
            $table->foreign('ordered_by_doctor_id', 'lab_results_ordered_by_doctor_id_foreign')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('reviewed_by_doctor_id', 'lab_results_reviewed_by_doctor_id_foreign')->references('id')->on('doctors')->onDelete('set null');

            // Add indexes for better performance
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'test_type']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'ordered_at']);
        });

        // Licenses
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

        // Generated reports
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');
            $table->string('file_name');
            $table->string('original_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('clinic_id');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->index(['clinic_id', 'generated_at']);
        });

        // Bills
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('encounter_id')->nullable();
            $table->unsignedBigInteger('clinic_id');
            $table->string('bill_number')->unique();
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('patient_id', 'bills_patient_id_foreign')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('encounter_id', 'bills_encounter_id_foreign')->references('id')->on('encounters')->onDelete('set null');
            $table->foreign('clinic_id', 'bills_clinic_id_foreign')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by', 'bills_created_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'bills_updated_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->index(['patient_id', 'status']);
            $table->index(['clinic_id', 'bill_date']);
            $table->index(['status', 'due_date']);
            $table->index('bill_number');
        });

        // Bill items
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('bill_id');
            $table->enum('item_type', ['service', 'medication', 'procedure', 'consultation', 'lab_test', 'other'])->default('service');
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->decimal('quantity', 8, 2)->default(1.00);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('bill_id', 'bill_items_bill_id_foreign')->references('id')->on('bills')->onDelete('cascade');
            $table->foreign('created_by', 'bill_items_created_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'bill_items_updated_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->index(['bill_id', 'item_type']);
            $table->index('item_name');
        });

        // Insurance
        Schema::create('insurance', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->string('insurance_provider');
            $table->string('policy_number');
            $table->string('group_number')->nullable();
            $table->string('member_id');
            $table->string('policy_holder_name');
            $table->enum('policy_holder_relationship', ['self', 'spouse', 'child', 'parent', 'sibling', 'other'])->default('self');
            $table->enum('coverage_type', ['health', 'dental', 'vision', 'pharmacy', 'other'])->default('health');
            $table->date('effective_date');
            $table->date('expiration_date')->nullable();
            $table->decimal('copay_amount', 8, 2)->default(0.00);
            $table->decimal('deductible_amount', 10, 2)->default(0.00);
            $table->decimal('coverage_percentage', 5, 2)->default(100.00);
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->timestamp('verification_date')->nullable();
            $table->text('verification_notes')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('patient_id', 'insurance_patient_id_foreign')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('created_by', 'insurance_created_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'insurance_updated_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->index(['patient_id', 'is_primary']);
            $table->index(['patient_id', 'is_active']);
            $table->index(['verification_status', 'expiration_date']);
            $table->index('insurance_provider');
            $table->index('policy_number');
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['info', 'success', 'warning', 'error', 'appointment', 'prescription', 'lab_result', 'system'])->default('info');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('delivery_method', ['database', 'email', 'sms', 'push'])->default('database');
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('expires_at')->nullable();
            $table->morphs('notifiable');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'notifications_user_id_foreign')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by', 'notifications_created_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'priority']);
            $table->index(['delivery_status', 'sent_at']);
            $table->index('expires_at');
        });

        // Queues
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('clinic_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('queue_type', ['general', 'emergency', 'appointment', 'walk_in', 'consultation', 'lab', 'pharmacy'])->default('general');
            $table->enum('status', ['active', 'paused', 'closed', 'maintenance'])->default('active');
            $table->integer('max_capacity')->default(50);
            $table->integer('current_count')->default(0);
            $table->integer('average_wait_time')->default(0); // in minutes
            $table->integer('estimated_wait_time')->default(0); // in minutes
            $table->integer('priority_level')->default(1); // 1-5 scale
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_assign')->default(false);
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id', 'queues_clinic_id_foreign')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by', 'queues_created_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'queues_updated_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->index(['clinic_id', 'is_active']);
            $table->index(['clinic_id', 'status']);
            $table->index(['queue_type', 'is_active']);
            $table->index('priority_level');
        });

        // Queue patients
        Schema::create('queue_patients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('queue_id');
            $table->unsignedBigInteger('patient_id');
            $table->integer('priority')->default(1); // 1-5 scale
            $table->enum('status', ['waiting', 'called', 'served', 'removed', 'no_show'])->default('waiting');
            $table->timestamp('joined_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->integer('estimated_wait_time')->default(0); // in minutes
            $table->integer('actual_wait_time')->default(0); // in minutes
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('queue_id', 'queue_patients_queue_id_foreign')->references('id')->on('queues')->onDelete('cascade');
            $table->foreign('patient_id', 'queue_patients_patient_id_foreign')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('created_by', 'queue_patients_created_by_foreign')->references('id')->on('users')->onDelete('set null');
            $table->index(['queue_id', 'status']);
            $table->index(['queue_id', 'priority']);
            $table->index(['patient_id', 'status']);
            $table->index(['status', 'joined_at']);
            $table->index('joined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('queue_patients');
        Schema::dropIfExists('queues');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('insurance');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('generated_reports');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('emrs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('medrep_visits');
        Schema::dropIfExists('medreps');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('file_assets');
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('user_clinic_roles');
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
