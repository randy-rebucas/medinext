<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members
     */
    public function index(Request $request)
    {
        try {
            $this->logWebRequest('Staff Management Access', ['action' => 'index']);
            
            $user = $request->user();
            if (!$user) {
                $this->logSecurityEvent('Unauthenticated staff access attempt');
                return redirect()->route('login');
            }

            $userClinicRole = $this->getUserClinicRole($request);
            $clinicId = $userClinicRole->clinic_id;

            $query = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $userClinicRole->clinic_id);
            })->with(['roles', 'clinics']);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            if ($request->has('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            $staff = $query->paginate($request->get('per_page', 15));

            // Get roles for the dropdown
            $roles = Role::where('is_system_role', false)->get();

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            return Inertia::render('admin/staff', [
                'staff' => $staff,
                'roles' => $roles,
                'permissions' => $permissions,
                'filters' => [
                    'search' => $request->search,
                    'role' => $request->role,
                    'status' => $request->status,
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::index');
            return redirect()->route('dashboard')->with('error', 'Failed to load staff management. Please try again.');
        }
    }

    /**
     * Display the specified staff member
     */
    public function show(Request $request, int $id): Response
    {
        try {
            $this->logWebRequest('Staff Management Access', ['action' => 'show', 'staff_id' => $id]);
            
            $user = $request->user();
            $userClinicRole = $this->getUserClinicRole($request);
            $clinicId = $userClinicRole->clinic_id;

            // Find staff member in current clinic
            $staff = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->with(['roles', 'clinics'])->findOrFail($id);

            // Get roles for the dropdown
            $roles = Role::where('is_system_role', false)->get();

            // Get user permissions
            $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

            return Inertia::render('admin/staff', [
                'staff' => collect([$staff]),
                'selectedStaff' => $staff,
                'roles' => $roles,
                'permissions' => $permissions,
                'filters' => [
                    'search' => '',
                    'role' => '',
                    'status' => '',
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::show');
            return redirect()->route('admin.staff')->with('error', 'Staff member not found.');
        }
    }

    /**
     * Store a newly created staff member
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:8',
                'role_id' => 'required|exists:roles,id',
                'is_active' => 'boolean',
                'clinic_id' => 'required|exists:clinics,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $clinicId = $request->clinic_id;
            $currentUserClinicId = $request->user()->current_clinic_id;

            // Check if user has permission to add staff to this clinic
            if ($currentUserClinicId !== $clinicId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only add staff to your current clinic'
                ], 403);
            }

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->get('is_active', true),
                'email_verified_at' => now(),
            ]);

            // Assign role
            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);

            // Assign to clinic
            $user->clinics()->attach($clinicId, [
                'role_id' => $request->role_id,
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);

            // Load relationships
            $user->load(['roles', 'clinics']);

            return response()->json([
                'success' => true,
                'message' => 'Staff member created successfully',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff member. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified staff member
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'password' => 'sometimes|string|min:8',
                'role_id' => 'sometimes|exists:roles,id',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userClinicRole = $this->getUserClinicRole($request);
            $clinicId = $userClinicRole->clinic_id;
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic selected'
                ], 400);
            }

            // Find user and check if they belong to current clinic
            $user = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->findOrFail($id);

            // Update user data
            $updateData = $request->only(['first_name', 'last_name', 'email', 'phone', 'is_active']);
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update role if provided
            if ($request->has('role_id')) {
                $role = Role::findOrFail($request->role_id);
                $user->syncRoles([$role]);
                
                // Update clinic role
                $user->clinics()->updateExistingPivot($clinicId, [
                    'role_id' => $request->role_id,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
            }

            // Load relationships
            $user->load(['roles', 'clinics']);

            return response()->json([
                'success' => true,
                'message' => 'Staff member updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff member. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified staff member from clinic
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $userClinicRole = $this->getUserClinicRole($request);
            $clinicId = $userClinicRole->clinic_id;
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic selected'
                ], 400);
            }

            // Find user and check if they belong to current clinic
            $user = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
            })->findOrFail($id);

            // Check if user is trying to remove themselves
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove yourself from the clinic'
                ], 403);
            }

            // Remove user from clinic
            $user->clinics()->detach($clinicId);

            // If user has no other clinics, deactivate them
            if ($user->clinics()->count() === 0) {
                $user->update(['is_active' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff member removed from clinic successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'StaffController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove staff member. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user permissions based on role
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
                'manage_staff', 'manage_doctors', 'view_appointments', 'view_patients',
                'view_reports', 'manage_settings', 'view_analytics', 'manage_clinic_settings',
                'view_financial_reports', 'manage_rooms', 'manage_schedules'
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