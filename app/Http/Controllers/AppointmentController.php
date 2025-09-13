<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\User;
use App\Models\UserClinicRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display the appointment management page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's clinic
        $userClinicRole = $user->userClinicRoles()->with(['clinic', 'role'])->first();

        if (!$userClinicRole) {
            return Inertia::render('admin/appointments', [
                'appointments' => [],
                'patients' => [],
                'doctors' => [],
                'rooms' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get appointments for this clinic
        $appointments = $this->getAppointments($clinicId);

        // Get related data
        $patients = $this->getPatients($clinicId);
        $doctors = $this->getDoctors($clinicId);
        $rooms = $this->getRooms($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/appointments', [
            'appointments' => $appointments,
            'patients' => $patients,
            'doctors' => $doctors,
            'rooms' => $rooms,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'type' => 'required|string|max:255',
            'status' => 'required|string|in:Scheduled,Confirmed,In Progress,Completed,Cancelled',
            'room_id' => 'nullable|exists:rooms,id',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'required|string|in:Low,Normal,High,Urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user from request
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userClinicRole = $user->userClinicRoles()->with(['clinic'])->first();

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;

            // Create appointment
            $appointment = Appointment::create([
                'clinic_id' => $clinicId,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'type' => $request->type,
                'status' => $request->status,
                'room_id' => $request->room_id,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'priority' => $request->priority,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment scheduled successfully',
                'appointment' => $this->getAppointment($appointment->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified appointment
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'type' => 'required|string|max:255',
            'status' => 'required|string|in:Scheduled,Confirmed,In Progress,Completed,Cancelled',
            'room_id' => 'nullable|exists:rooms,id',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'priority' => 'required|string|in:Low,Normal,High,Urgent',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $appointment = Appointment::findOrFail($id);

            // Update appointment
            $appointment->update([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'type' => $request->type,
                'status' => $request->status,
                'room_id' => $request->room_id,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'priority' => $request->priority,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully',
                'appointment' => $this->getAppointment($appointment->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified appointment
     */
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Appointment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appointment details
     */
    public function show($id)
    {
        try {
            $appointment = $this->getAppointment($id);

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Appointment retrieved successfully',
                'appointment' => $appointment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update appointment status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:Scheduled,Confirmed,In Progress,Completed,Cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated successfully',
                'appointment' => $this->getAppointment($appointment->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calendar data
     */
    public function calendar(Request $request)
    {
        try {
            $user = $request->user();
            $userClinicRole = $user->userClinicRoles()->with(['clinic'])->first();

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $startDate = $request->get('start', Carbon::now()->startOfMonth());
            $endDate = $request->get('end', Carbon::now()->endOfMonth());

            $appointments = Appointment::where('clinic_id', $clinicId)
                ->whereBetween('start_at', [$startDate, $endDate])
                ->with(['patient', 'doctor', 'room'])
                ->get()
                ->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'title' => $appointment->patient->name . ' - ' . $appointment->doctor->user->name,
                        'start' => $appointment->start_at,
                        'end' => $appointment->end_at,
                        'status' => $appointment->status,
                        'type' => $appointment->type,
                        'room' => $appointment->room->name ?? 'No Room',
                        'patient' => $appointment->patient->name,
                        'doctor' => $appointment->doctor->user->name,
                        'reason' => $appointment->reason,
                        'notes' => $appointment->notes,
                    ];
                });

            return response()->json([
                'success' => true,
                'appointments' => $appointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calendar data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appointments for a clinic
     */
    private function getAppointments($clinicId)
    {
        return Appointment::where('clinic_id', $clinicId)
            ->with(['patient', 'doctor.user', 'room'])
            ->orderBy('start_at', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->name,
                    'patient_id' => $appointment->patient->patient_id,
                    'patient_email' => $appointment->patient->contact['email'] ?? '',
                    'patient_phone' => $appointment->patient->contact['phone'] ?? '',
                    'doctor_name' => $appointment->doctor->user->name,
                    'doctor_specialization' => $appointment->doctor->specialization,
                    'start_at' => $appointment->start_at,
                    'end_at' => $appointment->end_at,
                    'date' => Carbon::parse($appointment->start_at)->format('Y-m-d'),
                    'time' => Carbon::parse($appointment->start_at)->format('H:i'),
                    'duration' => Carbon::parse($appointment->start_at)->diffInMinutes(Carbon::parse($appointment->end_at)),
                    'type' => $appointment->type,
                    'status' => $appointment->status,
                    'room_name' => $appointment->room->name ?? 'No Room',
                    'room_id' => $appointment->room_id,
                    'reason' => $appointment->reason,
                    'notes' => $appointment->notes,
                    'priority' => $appointment->priority ?? 'Normal',
                    'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $appointment->updated_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Get a single appointment
     */
    private function getAppointment($appointmentId)
    {
        $appointment = Appointment::with(['patient', 'doctor.user', 'room'])->findOrFail($appointmentId);

        return [
            'id' => $appointment->id,
            'patient_name' => $appointment->patient->name,
            'patient_id' => $appointment->patient->patient_id,
            'patient_email' => $appointment->patient->contact['email'] ?? '',
            'patient_phone' => $appointment->patient->contact['phone'] ?? '',
            'doctor_name' => $appointment->doctor->user->name,
            'doctor_specialization' => $appointment->doctor->specialization,
            'start_at' => $appointment->start_at,
            'end_at' => $appointment->end_at,
            'date' => Carbon::parse($appointment->start_at)->format('Y-m-d'),
            'time' => Carbon::parse($appointment->start_at)->format('H:i'),
            'duration' => Carbon::parse($appointment->start_at)->diffInMinutes(Carbon::parse($appointment->end_at)),
            'type' => $appointment->type,
            'status' => $appointment->status,
            'room_name' => $appointment->room->name ?? 'No Room',
            'room_id' => $appointment->room_id,
            'reason' => $appointment->reason,
            'notes' => $appointment->notes,
            'priority' => $appointment->priority ?? 'Normal',
            'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $appointment->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get patients for a clinic
     */
    private function getPatients($clinicId)
    {
        return Patient::where('clinic_id', $clinicId)
            ->get(['id', 'name', 'patient_id', 'contact'])
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'patient_id' => $patient->patient_id,
                    'email' => $patient->contact['email'] ?? '',
                    'phone' => $patient->contact['phone'] ?? '',
                ];
            });
    }

    /**
     * Get doctors for a clinic
     */
    private function getDoctors($clinicId)
    {
        return Doctor::where('clinic_id', $clinicId)
            ->with('user')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'specialization' => $doctor->specialization,
                ];
            });
    }

    /**
     * Get rooms for a clinic
     */
    private function getRooms($clinicId)
    {
        return Room::where('clinic_id', $clinicId)
            ->get(['id', 'name', 'room_number', 'room_type'])
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type,
                ];
            });
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_users', 'manage_clinics', 'manage_licenses', 'view_analytics',
                'manage_settings', 'view_activity_logs', 'manage_roles', 'manage_permissions',
                'view_system_health', 'manage_backups', 'view_financial_reports'
            ],
            'admin' => [
                // Clinic Management
                'manage_clinics', 'view_clinics', 'create_clinics', 'edit_clinics', 'delete_clinics',
                
                // User & Staff Management
                'manage_users', 'view_users', 'create_users', 'edit_users', 'delete_users',
                'manage_staff', 'view_staff', 'create_staff', 'edit_staff', 'delete_staff',
                
                // Doctor Management
                'manage_doctors', 'view_doctors', 'create_doctors', 'edit_doctors', 'delete_doctors',
                
                // Patient Management
                'manage_patients', 'view_patients', 'create_patients', 'edit_patients', 'delete_patients',
                
                // Appointment Management
                'manage_appointments', 'view_appointments', 'create_appointments', 'edit_appointments',
                'cancel_appointments', 'delete_appointments', 'checkin_appointments',
                
                // Prescription Management
                'manage_prescriptions', 'view_prescriptions', 'create_prescriptions', 'edit_prescriptions',
                'delete_prescriptions', 'download_prescriptions',
                
                // Medical Records
                'manage_medical_records', 'view_medical_records', 'create_medical_records',
                'edit_medical_records', 'delete_medical_records',
                
                // Queue Management
                'manage_queue', 'view_queue', 'add_queue', 'remove_queue', 'process_queue',
                
                // Room Management
                'manage_rooms', 'view_rooms', 'create_rooms', 'edit_rooms', 'delete_rooms',
                
                // Schedule Management
                'manage_schedules', 'view_schedules',
                
                // Billing & Financial
                'manage_billing', 'view_billing', 'create_billing', 'edit_billing', 'delete_billing',
                'view_financial_reports', 'export_financial_reports',
                
                // Reports & Analytics
                'view_reports', 'export_reports', 'generate_reports', 'view_analytics',
                'view_activity_logs', 'export_activity_logs',
                
                // File Management
                'manage_file_assets', 'view_file_assets', 'upload_file_assets',
                'download_file_assets', 'delete_file_assets',
                
                // Notifications
                'manage_notifications', 'view_notifications', 'create_notifications',
                'edit_notifications', 'delete_notifications',
                
                // Settings
                'manage_settings', 'view_settings', 'manage_clinic_settings',
                
                // Dashboard & Search
                'view_dashboard', 'view_dashboard_stats', 'global_search',
                'search_patients', 'search_doctors',
                
                // Profile
                'view_profile', 'edit_profile'
            ],
            'doctor' => [
                'work_on_queue', 'view_appointments', 'manage_prescriptions', 'view_medical_records',
                'view_patients', 'view_analytics', 'manage_encounters', 'view_lab_results',
                'manage_treatment_plans', 'view_patient_history', 'manage_soap_notes'
            ],
            'receptionist' => [
                'search_patients', 'manage_appointments', 'manage_queue', 'register_patients',
                'view_encounters', 'view_reports', 'manage_patient_info', 'view_appointments',
                'manage_check_in', 'view_patient_history', 'manage_insurance'
            ],
            'patient' => [
                'book_appointments', 'view_medical_records', 'view_prescriptions', 'view_lab_results',
                'view_appointments', 'update_profile', 'download_documents', 'view_billing',
                'manage_notifications', 'view_insurance', 'schedule_follow_ups'
            ],
            'medrep' => [
                'manage_products', 'schedule_meetings', 'track_interactions', 'manage_doctors',
                'view_analytics', 'manage_samples', 'view_meeting_history', 'manage_territory',
                'view_performance_metrics', 'manage_marketing_materials', 'track_commitments'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
