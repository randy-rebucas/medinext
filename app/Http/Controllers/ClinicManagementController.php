<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\User;
use App\Models\Role;
use App\Models\UserClinicRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClinicManagementController extends Controller
{
    /**
     * Display a listing of clinics for the current user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $clinics = $user->clinics()
            ->with(['userClinicRoles.role'])
            ->withCount(['doctors', 'patients', 'appointments'])
            ->get()
            ->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'slug' => $clinic->slug,
                    'address' => $clinic->formatted_address ?? 'Address not specified',
                    'phone' => $clinic->phone,
                    'email' => $clinic->email,
                    'logo_url' => $clinic->logo_url,
                    'description' => $clinic->description,
                    'created_at' => $clinic->created_at,
                    'updated_at' => $clinic->updated_at,
                    'doctors_count' => $clinic->doctors_count,
                    'patients_count' => $clinic->patients_count,
                    'appointments_count' => $clinic->appointments_count,
                    'user_clinic_roles' => $clinic->userClinicRoles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'role' => [
                                'name' => $role->role->name
                            ]
                        ];
                    })
                ];
            });

        $currentClinic = $user->getCurrentClinic();
        $currentClinicData = null;
        if ($currentClinic) {
            $currentClinicData = [
                'id' => $currentClinic->id,
                'name' => $currentClinic->name,
                'slug' => $currentClinic->slug,
                'address' => $currentClinic->formatted_address ?? 'Address not specified',
                'phone' => $currentClinic->phone,
                'email' => $currentClinic->email,
                'logo_url' => $currentClinic->logo_url,
                'description' => $currentClinic->description,
                'created_at' => $currentClinic->created_at,
                'updated_at' => $currentClinic->updated_at,
                'doctors_count' => $currentClinic->doctors()->count(),
                'patients_count' => $currentClinic->patients()->count(),
                'appointments_count' => $currentClinic->appointments()->count(),
                'user_clinic_roles' => $currentClinic->userClinicRoles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'role' => [
                            'name' => $role->role->name
                        ]
                    ];
                })
            ];
        }

        return Inertia::render('admin/clinic-management', [
            'clinics' => $clinics,
            'currentClinic' => $currentClinicData
        ]);
    }

    /**
     * Show the form for creating a new clinic
     */
    public function create()
    {
        return Inertia::render('admin/clinic-create');
    }

    /**
     * Store a newly created clinic
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:clinics,slug',
            'timezone' => 'required|string|max:100',
            'logo_url' => 'nullable|url|max:500',
            'address' => 'nullable|array',
            'address.street' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:100',
            'address.state' => 'nullable|string|max:100',
            'address.postal_code' => 'nullable|string|max:20',
            'address.country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();

            // Auto-generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
                $counter = 1;
                while (Clinic::where('slug', $data['slug'])->exists()) {
                    $data['slug'] = Str::slug($data['name']) . '-' . $counter;
                    $counter++;
                }
            }

            $clinic = Clinic::create($data);

            // Assign the creating user as admin of the new clinic
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                UserClinicRole::create([
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $adminRole->id,
                    'assigned_at' => now(),
                ]);
            }

            // Set as current clinic
            session(['current_clinic_id' => $clinic->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Clinic created successfully',
                'data' => [
                    'clinic' => $clinic,
                    'redirect_url' => '/admin/dashboard'
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create clinic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified clinic
     */
    public function show(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has access to this clinic
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            abort(403, 'You do not have access to this clinic.');
        }

        $clinic->load(['doctors.user', 'appointments.patient', 'userClinicRoles.user']);

        $stats = [
            'total_doctors' => $clinic->doctors()->count(),
            'total_patients' => $clinic->patients()->count(),
            'total_appointments' => $clinic->appointments()->count(),
            'total_encounters' => $clinic->encounters()->count(),
            'total_staff' => $clinic->userClinicRoles()->count(),
        ];

        return Inertia::render('admin/clinic-details', [
            'clinic' => $clinic,
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for editing the specified clinic
     */
    public function edit(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to edit this clinic
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            abort(403, 'Insufficient permissions to edit this clinic.');
        }

        return Inertia::render('admin/clinic-edit', [
            'clinic' => $clinic
        ]);
    }

    /**
     * Update the specified clinic
     */
    public function update(Request $request, Clinic $clinic): JsonResponse
    {
        $user = $request->user();

        // Check if user has permission to edit this clinic
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to edit this clinic'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:clinics,slug,' . $clinic->id,
            'timezone' => 'required|string|max:100',
            'logo_url' => 'nullable|url|max:500',
            'address' => 'nullable|array',
            'address.street' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:100',
            'address.state' => 'nullable|string|max:100',
            'address.postal_code' => 'nullable|string|max:20',
            'address.country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clinic->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Clinic updated successfully',
                'data' => [
                    'clinic' => $clinic->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update clinic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified clinic
     */
    public function destroy(Request $request, Clinic $clinic): JsonResponse
    {
        $user = $request->user();

        // Check if user has permission to delete this clinic
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to delete this clinic'
            ], 403);
        }

        // Check if clinic has any data
        $hasData = $clinic->patients()->exists() ||
                  $clinic->appointments()->exists() ||
                  $clinic->encounters()->exists();

        if ($hasData) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete clinic with existing data. Please transfer or remove all data first.'
            ], 422);
        }

        try {
            $clinic->delete();

            // If this was the current clinic, clear the session
            if (session('current_clinic_id') == $clinic->id) {
                session()->forget('current_clinic_id');
            }

            return response()->json([
                'success' => true,
                'message' => 'Clinic deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete clinic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show clinic members
     */
    public function members(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to manage clinic members
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            abort(403, 'Insufficient permissions to manage clinic members.');
        }

        $members = $clinic->userClinicRoles()
            ->with(['user', 'role'])
            ->get();

        $availableUsers = User::whereDoesntHave('userClinicRoles', function ($query) use ($clinic) {
            $query->where('clinic_id', $clinic->id);
        })->get();

        $roles = Role::all();

        return Inertia::render('admin/clinic-members', [
            'clinic' => $clinic,
            'members' => $members,
            'availableUsers' => $availableUsers,
            'roles' => $roles
        ]);
    }

    /**
     * Add member to clinic
     */
    public function addMember(Request $request, Clinic $clinic): JsonResponse
    {
        $user = $request->user();

        // Check if user has permission to manage clinic members
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to manage clinic members'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is already a member
        $existing = UserClinicRole::where('user_id', $request->user_id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this clinic'
            ], 422);
        }

        try {
            UserClinicRole::create([
                'user_id' => $request->user_id,
                'clinic_id' => $clinic->id,
                'role_id' => $request->role_id,
                'assigned_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove member from clinic
     */
    public function removeMember(Request $request, Clinic $clinic, User $member): JsonResponse
    {
        $user = $request->user();

        // Check if user has permission to manage clinic members
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to manage clinic members'
            ], 403);
        }

        // Prevent removing the last admin
        $adminCount = $clinic->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'superadmin']);
            })
            ->count();

        $userRole = $clinic->userClinicRoles()
            ->where('user_id', $member->id)
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'superadmin']);
            })
            ->first();

        if ($userRole && $adminCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the last administrator from the clinic'
            ], 422);
        }

        try {
            $clinic->userClinicRoles()
                ->where('user_id', $member->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clinic statistics
     */
    public function statistics(Request $request, Clinic $clinic): JsonResponse
    {
        $user = $request->user();

        // Check if user has access to this clinic
        if (!$user->hasRoleInClinic('admin', $clinic->id) && 
            !$user->hasRoleInClinic('superadmin', $clinic->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this clinic'
            ], 403);
        }

        $statistics = [
            'total_users' => $clinic->userClinicRoles()->count(),
            'total_doctors' => $clinic->doctors()->count(),
            'total_patients' => $clinic->patients()->count(),
            'total_appointments' => $clinic->appointments()->count(),
            'total_encounters' => $clinic->encounters()->count(),
            'total_prescriptions' => $clinic->prescriptions()->count(),
            'total_lab_results' => $clinic->labResults()->count(),

            'appointments_today' => $clinic->appointments()
                ->whereDate('start_at', now()->toDateString())
                ->count(),
            'appointments_this_week' => $clinic->appointments()
                ->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'appointments_this_month' => $clinic->appointments()
                ->whereBetween('start_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),

            'new_patients_this_month' => $clinic->patients()
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),

            'appointments_by_status' => $clinic->appointments()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),

            'appointments_by_type' => $clinic->appointments()
                ->selectRaw('appointment_type, COUNT(*) as count')
                ->groupBy('appointment_type')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $statistics,
                'clinic' => $clinic
            ]
        ]);
    }
}
