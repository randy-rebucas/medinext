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
        // Add indexes to roles table
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasIndex('roles', 'roles_name_index')) {
                $table->index('name');
            }
        });

        // Add indexes to permissions table
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasIndex('permissions', 'permissions_module_index')) {
                $table->index('module');
            }
            if (!Schema::hasIndex('permissions', 'permissions_action_index')) {
                $table->index('action');
            }
            if (!Schema::hasIndex('permissions', 'permissions_module_action_index')) {
                $table->index(['module', 'action']);
            }
        });

        // Add indexes to doctors table
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasIndex('doctors', 'doctors_user_id_index')) {
                $table->index('user_id');
            }
            if (!Schema::hasIndex('doctors', 'doctors_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('doctors', 'doctors_clinic_id_user_id_index')) {
                $table->index(['clinic_id', 'user_id']);
            }
        });

        // Add indexes to patients table
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasIndex('patients', 'patients_clinic_id_index')) {
                $table->index('clinic_id');
            }

            if (!Schema::hasIndex('patients', 'patients_first_name_last_name_index')) {
                $table->index(['first_name', 'last_name']);
            }
            if (!Schema::hasIndex('patients', 'patients_dob_index')) {
                $table->index('dob');
            }

        });

        // Add indexes to rooms table
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasIndex('rooms', 'rooms_clinic_id_index')) {
                $table->index('clinic_id');
            }
        });

        // Add indexes to encounters table
        Schema::table('encounters', function (Blueprint $table) {
            if (!Schema::hasIndex('encounters', 'encounters_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('encounters', 'encounters_patient_id_index')) {
                $table->index('patient_id');
            }
            if (!Schema::hasIndex('encounters', 'encounters_doctor_id_index')) {
                $table->index('doctor_id');
            }
            if (!Schema::hasIndex('encounters', 'encounters_date_index')) {
                $table->index('date');
            }
            if (!Schema::hasIndex('encounters', 'encounters_clinic_id_patient_id_index')) {
                $table->index(['clinic_id', 'patient_id']);
            }
            if (!Schema::hasIndex('encounters', 'encounters_clinic_id_doctor_id_index')) {
                $table->index(['clinic_id', 'doctor_id']);
            }
        });

        // Add indexes to file_assets table
        Schema::table('file_assets', function (Blueprint $table) {
            if (!Schema::hasIndex('file_assets', 'file_assets_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('file_assets', 'file_assets_owner_type_index')) {
                $table->index('owner_type');
            }
            if (!Schema::hasIndex('file_assets', 'file_assets_owner_id_index')) {
                $table->index('owner_id');
            }
            if (!Schema::hasIndex('file_assets', 'file_assets_owner_type_owner_id_index')) {
                $table->index(['owner_type', 'owner_id']);
            }
            if (!Schema::hasIndex('file_assets', 'file_assets_mime_index')) {
                $table->index('mime');
            }
        });

        // Add indexes to appointments table
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasIndex('appointments', 'appointments_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('appointments', 'appointments_patient_id_index')) {
                $table->index('patient_id');
            }
            if (!Schema::hasIndex('appointments', 'appointments_doctor_id_index')) {
                $table->index('doctor_id');
            }
            if (!Schema::hasIndex('appointments', 'appointments_start_at_index')) {
                $table->index('start_at');
            }
            if (!Schema::hasIndex('appointments', 'appointments_end_at_index')) {
                $table->index('end_at');
            }
            if (!Schema::hasIndex('appointments', 'appointments_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('appointments', 'appointments_room_id_index')) {
                $table->index('room_id');
            }
            if (!Schema::hasIndex('appointments', 'appointments_clinic_id_start_at_index')) {
                $table->index(['clinic_id', 'start_at']);
            }
            if (!Schema::hasIndex('appointments', 'appointments_clinic_id_status_index')) {
                $table->index(['clinic_id', 'status']);
            }
            if (!Schema::hasIndex('appointments', 'appointments_patient_id_start_at_index')) {
                $table->index(['patient_id', 'start_at']);
            }
        });

        // Add indexes to prescriptions table
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasIndex('prescriptions', 'prescriptions_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_patient_id_index')) {
                $table->index('patient_id');
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_doctor_id_index')) {
                $table->index('doctor_id');
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_encounter_id_index')) {
                $table->index('encounter_id');
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_issued_at_index')) {
                $table->index('issued_at');
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_clinic_id_patient_id_index')) {
                $table->index(['clinic_id', 'patient_id']);
            }
            if (!Schema::hasIndex('prescriptions', 'prescriptions_clinic_id_issued_at_index')) {
                $table->index(['clinic_id', 'issued_at']);
            }
        });

        // Add indexes to prescription_items table
        Schema::table('prescription_items', function (Blueprint $table) {
            if (!Schema::hasIndex('prescription_items', 'prescription_items_prescription_id_index')) {
                $table->index('prescription_id');
            }
        });

        // Add indexes to activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasIndex('activity_logs', 'activity_logs_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_actor_user_id_index')) {
                $table->index('actor_user_id');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_entity_index')) {
                $table->index('entity');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_entity_id_index')) {
                $table->index('entity_id');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_at_index')) {
                $table->index('at');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_action_index')) {
                $table->index('action');
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_entity_entity_id_index')) {
                $table->index(['entity', 'entity_id']);
            }
            if (!Schema::hasIndex('activity_logs', 'activity_logs_clinic_id_at_index')) {
                $table->index(['clinic_id', 'at']);
            }
        });

        // Add indexes to medreps table
        Schema::table('medreps', function (Blueprint $table) {
            if (!Schema::hasIndex('medreps', 'medreps_user_id_index')) {
                $table->index('user_id');
            }
            if (!Schema::hasIndex('medreps', 'medreps_company_index')) {
                $table->index('company');
            }
        });

        // Add indexes to medrep_visits table
        Schema::table('medrep_visits', function (Blueprint $table) {
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_medrep_id_index')) {
                $table->index('medrep_id');
            }
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_doctor_id_index')) {
                $table->index('doctor_id');
            }
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_start_at_index')) {
                $table->index('start_at');
            }
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_end_at_index')) {
                $table->index('end_at');
            }
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_clinic_id_start_at_index')) {
                $table->index(['clinic_id', 'start_at']);
            }
            if (!Schema::hasIndex('medrep_visits', 'medrep_visits_medrep_id_start_at_index')) {
                $table->index(['medrep_id', 'start_at']);
            }
        });

        // Add indexes to settings table
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasIndex('settings', 'settings_clinic_id_index')) {
                $table->index('clinic_id');
            }
            if (!Schema::hasIndex('settings', 'settings_key_index')) {
                $table->index('key');
            }
            if (!Schema::hasIndex('settings', 'settings_group_index')) {
                $table->index('group');
            }
            if (!Schema::hasIndex('settings', 'settings_clinic_id_key_index')) {
                $table->index(['clinic_id', 'key']);
            }
            if (!Schema::hasIndex('settings', 'settings_clinic_id_group_index')) {
                $table->index(['clinic_id', 'group']);
            }
        });

        // Add indexes to emrs table
        Schema::table('emrs', function (Blueprint $table) {
            if (!Schema::hasIndex('emrs', 'emrs_patient_id_index')) {
                $table->index('patient_id');
            }
            if (!Schema::hasIndex('emrs', 'emrs_doctor_id_index')) {
                $table->index('doctor_id');
            }
            if (!Schema::hasIndex('emrs', 'emrs_appointment_id_index')) {
                $table->index('appointment_id');
            }
            if (!Schema::hasIndex('emrs', 'emrs_status_index')) {
                $table->index('status');
            }
            if (!Schema::hasIndex('emrs', 'emrs_visit_type_index')) {
                $table->index('visit_type');
            }
            if (!Schema::hasIndex('emrs', 'emrs_patient_id_status_index')) {
                $table->index(['patient_id', 'status']);
            }
            if (!Schema::hasIndex('emrs', 'emrs_doctor_id_status_index')) {
                $table->index(['doctor_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        // Remove indexes from permissions table
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropIndex(['action']);
            $table->dropIndex(['module', 'action']);
        });

        // Remove indexes from clinics table
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        // Remove indexes from doctors table
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['clinic_id', 'user_id']);
        });

        // Remove indexes from patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['code']);
            $table->dropIndex(['first_name', 'last_name']);
            $table->dropIndex(['dob']);
            $table->dropIndex(['sex']);
        });

        // Remove indexes from rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['type']);
        });

        // Remove indexes from encounters table
        Schema::table('encounters', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['clinic_id', 'patient_id']);
            $table->dropIndex(['clinic_id', 'doctor_id']);
        });

        // Remove indexes from file_assets table
        Schema::table('file_assets', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['owner_type']);
            $table->dropIndex(['owner_id']);
            $table->dropIndex(['owner_type', 'owner_id']);
            $table->dropIndex(['mime']);
        });

        // Remove indexes from appointments table
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['start_at']);
            $table->dropIndex(['end_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['room_id']);
            $table->dropIndex(['clinic_id', 'start_at']);
            $table->dropIndex(['clinic_id', 'status']);
            $table->dropIndex(['patient_id', 'start_at']);
        });

        // Remove indexes from prescriptions table
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['encounter_id']);
            $table->dropIndex(['issued_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['clinic_id', 'patient_id']);
            $table->dropIndex(['clinic_id', 'issued_at']);
        });

        // Remove indexes from prescription_items table
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropIndex(['prescription_id']);
            $table->dropIndex(['drug_name']);
        });

        // Remove indexes from activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['actor_user_id']);
            $table->dropIndex(['entity']);
            $table->dropIndex(['entity_id']);
            $table->dropIndex(['at']);
            $table->dropIndex(['action']);
            $table->dropIndex(['entity', 'entity_id']);
            $table->dropIndex(['clinic_id', 'at']);
        });

        // Remove indexes from medreps table
        Schema::table('medreps', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['company']);
        });

        // Remove indexes from medrep_visits table
        Schema::table('medrep_visits', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['medrep_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['start_at']);
            $table->dropIndex(['end_at']);
            $table->dropIndex(['clinic_id', 'start_at']);
            $table->dropIndex(['medrep_id', 'start_at']);
        });

        // Remove indexes from settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['clinic_id']);
            $table->dropIndex(['key']);
            $table->dropIndex(['group']);
            $table->dropIndex(['clinic_id', 'key']);
            $table->dropIndex(['clinic_id', 'group']);
        });

        // Remove indexes from emrs table
        Schema::table('emrs', function (Blueprint $table) {
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['doctor_id']);
            $table->dropIndex(['appointment_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['visit_type']);
            $table->dropIndex(['patient_id', 'status']);
            $table->dropIndex(['doctor_id', 'status']);
        });
    }
};
