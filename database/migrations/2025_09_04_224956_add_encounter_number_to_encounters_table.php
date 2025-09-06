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
        // Check if columns already exist to avoid errors
        if (!Schema::hasColumn('encounters', 'encounter_number')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->string('encounter_number')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'chief_complaint')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->string('chief_complaint')->nullable()->after('encounter_number');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'assessment')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->text('assessment')->nullable()->after('chief_complaint');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'plan')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->text('plan')->nullable()->after('assessment');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'follow_up_date')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->timestamp('follow_up_date')->nullable()->after('plan');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'visit_type')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->string('visit_type')->nullable()->after('follow_up_date');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'payment_status')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->string('payment_status')->default('pending')->after('visit_type');
            });
        }
        
        if (!Schema::hasColumn('encounters', 'billing_amount')) {
            Schema::table('encounters', function (Blueprint $table) {
                $table->decimal('billing_amount', 10, 2)->nullable()->after('payment_status');
            });
        }

        // Generate encounter numbers for existing records that don't have them
        $encounters = \DB::table('encounters')->where(function($query) {
            $query->whereNull('encounter_number')->orWhere('encounter_number', '');
        })->get();
        
        foreach ($encounters as $encounter) {
            $encounterNumber = 'ENC-' . str_pad($encounter->id, 6, '0', STR_PAD_LEFT);
            \DB::table('encounters')->where('id', $encounter->id)->update(['encounter_number' => $encounterNumber]);
        }

        // Add unique constraint only if it doesn't already exist
        try {
            \DB::statement('ALTER TABLE encounters ADD UNIQUE KEY encounters_encounter_number_unique (encounter_number)');
        } catch (\Exception $e) {
            // Unique constraint already exists, ignore the error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encounters', function (Blueprint $table) {
            if (Schema::hasColumn('encounters', 'encounter_number')) {
                $table->dropUnique(['encounter_number']);
            }
            if (Schema::hasColumn('encounters', 'encounter_number')) {
                $table->dropColumn('encounter_number');
            }
            if (Schema::hasColumn('encounters', 'chief_complaint')) {
                $table->dropColumn('chief_complaint');
            }
            if (Schema::hasColumn('encounters', 'assessment')) {
                $table->dropColumn('assessment');
            }
            if (Schema::hasColumn('encounters', 'plan')) {
                $table->dropColumn('plan');
            }
            if (Schema::hasColumn('encounters', 'follow_up_date')) {
                $table->dropColumn('follow_up_date');
            }
            if (Schema::hasColumn('encounters', 'visit_type')) {
                $table->dropColumn('visit_type');
            }
            if (Schema::hasColumn('encounters', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('encounters', 'billing_amount')) {
                $table->dropColumn('billing_amount');
            }
        });
    }
};
