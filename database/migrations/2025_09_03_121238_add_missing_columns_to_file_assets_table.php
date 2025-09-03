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
            $table->string('category')->nullable()->after('checksum');
            $table->text('description')->nullable()->after('category');
            $table->string('file_name')->nullable()->after('description');
            $table->string('original_name')->nullable()->after('file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_assets', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'file_name', 'original_name']);
        });
    }
};
