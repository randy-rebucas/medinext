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
        Schema::table('user_clinic_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_by')->nullable()->after('role_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            
            // Add foreign key constraint for assigned_by
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_clinic_roles', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assigned_by', 'assigned_at']);
        });
    }
};