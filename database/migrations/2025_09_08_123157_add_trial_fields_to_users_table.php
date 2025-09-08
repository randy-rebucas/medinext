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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_started_at')->nullable()->after('email_verified_at');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->string('license_key')->nullable()->after('trial_ends_at');
            $table->boolean('is_trial_user')->default(true)->after('license_key');
            $table->boolean('has_activated_license')->default(false)->after('is_trial_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'trial_started_at',
                'trial_ends_at',
                'license_key',
                'is_trial_user',
                'has_activated_license'
            ]);
        });
    }
};
