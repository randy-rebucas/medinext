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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
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
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['bill_id', 'item_type']);
            $table->index('item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};