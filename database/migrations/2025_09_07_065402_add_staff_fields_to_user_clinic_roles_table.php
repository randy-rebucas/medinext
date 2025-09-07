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
            $table->string('department')->nullable()->after('role_id');
            $table->string('status')->default('Active')->after('department');
            $table->text('address')->nullable()->after('status');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('emergency_phone')->nullable()->after('emergency_contact');
            $table->text('notes')->nullable()->after('emergency_phone');
            $table->date('join_date')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_clinic_roles', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'status',
                'address',
                'emergency_contact',
                'emergency_phone',
                'notes',
                'join_date'
            ]);
        });
    }
};
