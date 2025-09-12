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

class StaffController extends Controller
{
    /**
     * Display a listing of staff members
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $clinicId = $request->user()->current_clinic_id;
            
            if (!$clinicId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No clinic selected'
                ], 400);
            }

            $query = User::whereHas('clinics', function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId);
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

            return response()->json([
                'success' => true,
                'data' => $staff
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve staff members',
                'error' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff member',
                'error' => $e->getMessage()
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

            $clinicId = $request->user()->current_clinic_id;
            
            if (!$clinicId) {
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified staff member from clinic
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $clinicId = $request->user()->current_clinic_id;
            
            if (!$clinicId) {
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}