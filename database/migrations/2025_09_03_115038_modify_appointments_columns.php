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
            $table->string('status', 50)->default('booked')->change(); // booked, arrived, in-room, completed, no-show, canceled
            $table->string('reason', 500)->nullable()->change();
            $table->string('source', 50)->nullable()->change(); // patient, receptionist, system
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status', 50)->default('booked')->change();
            $table->text('reason')->nullable()->change();
            $table->text('source')->nullable()->change();
        });
    }
};
