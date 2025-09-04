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
        Schema::table('doctors', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('signature_url');
            $table->decimal('consultation_fee', 10, 2)->nullable()->after('is_active');
            $table->json('availability_schedule')->nullable()->after('consultation_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'consultation_fee', 'availability_schedule']);
        });
    }
};
