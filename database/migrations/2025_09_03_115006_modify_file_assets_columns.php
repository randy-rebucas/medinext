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
        Schema::table('file_assets', function (Blueprint $table) {
            $table->string('owner_type', 100)->change(); // patient, doctor, prescription, etc.
            $table->string('url', 500)->change();
            $table->string('mime', 100)->nullable()->change();
            $table->string('checksum', 64)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_assets', function (Blueprint $table) {
            $table->text('owner_type')->change();
            $table->text('url')->change();
            $table->text('mime')->nullable()->change();
            $table->text('checksum')->change();
        });
    }
};
