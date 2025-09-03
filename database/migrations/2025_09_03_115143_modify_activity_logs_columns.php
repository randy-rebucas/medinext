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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('entity', 100)->change();
            $table->string('action', 100)->change();
            $table->string('ip', 45)->nullable()->change(); // IPv6 can be up to 45 characters
            $table->string('before_hash', 64)->nullable()->change();
            $table->string('after_hash', 64)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->text('entity')->change();
            $table->text('action')->change();
            $table->text('ip')->nullable()->change();
            $table->text('before_hash')->nullable()->change();
            $table->text('after_hash')->nullable()->change();
        });
    }
};
