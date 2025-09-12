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
        Schema::create('insurance', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
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
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['patient_id', 'is_primary']);
            $table->index(['patient_id', 'is_active']);
            $table->index(['verification_status', 'expiration_date']);
            $table->index('insurance_provider');
            $table->index('policy_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance');
    }
};