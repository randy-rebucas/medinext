<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClinicSwitchController extends Controller
{
    /**
     * Show clinic selection page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $clinics = $user->clinics()->with(['userClinicRoles.role'])->get();

        return Inertia::render('clinic-selection', [
            'clinics' => $clinics,
            'currentClinic' => $user->getCurrentClinic()
        ]);
    }

    /**
     * Switch to a different clinic
     */
    public function switch(Request $request): JsonResponse
    {
        $user = $request->user();
        $clinicId = $request->input('clinic_id');

        if (!$clinicId) {
            return response()->json([
                'success' => false,
                'message' => 'Clinic ID is required'
            ], 400);
        }

        // Check if user has access to this clinic
        if (!$user->hasRoleInClinic('admin', $clinicId) && 
            !$user->hasRoleInClinic('superadmin', $clinicId) &&
            !$user->hasRoleInClinic('doctor', $clinicId) &&
            !$user->hasRoleInClinic('receptionist', $clinicId) &&
            !$user->hasRoleInClinic('nurse', $clinicId)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this clinic'
            ], 403);
        }

        $clinic = Clinic::findOrFail($clinicId);

        // Set current clinic in session and database
        session(['current_clinic_id' => $clinicId]);
        $user->update(['current_clinic_id' => $clinicId]);

        return response()->json([
            'success' => true,
            'message' => 'Clinic switched successfully',
            'data' => [
                'clinic' => $clinic,
                'redirect_url' => $request->input('redirect_url', '/admin/dashboard')
            ]
        ]);
    }

    /**
     * Get current clinic information
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentClinic = $user->getCurrentClinic();

        if (!$currentClinic) {
            return response()->json([
                'success' => false,
                'message' => 'No clinic selected'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'clinic' => $currentClinic,
                'user_role' => $user->userClinicRoles()
                    ->where('clinic_id', $currentClinic->id)
                    ->with('role')
                    ->first()
            ]
        ]);
    }

    /**
     * Get all clinics for the current user
     */
    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $clinics = $user->clinics()
            ->with(['userClinicRoles.role'])
            ->get()
            ->map(function ($clinic) use ($user) {
                $userRole = $clinic->userClinicRoles()
                    ->where('user_id', $user->id)
                    ->with('role')
                    ->first();

                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'slug' => $clinic->slug,
                    'address' => $clinic->formatted_address ?? 'Address not specified',
                    'phone' => $clinic->phone,
                    'email' => $clinic->email,
                    'logo_url' => $clinic->logo_url,
                    'user_role' => $userRole ? $userRole->role->name : null,
                    'is_current' => ($user->current_clinic_id == $clinic->id) || (session('current_clinic_id') == $clinic->id),
                    'statistics' => [
                        'total_doctors' => $clinic->doctors()->count(),
                        'total_patients' => $clinic->patients()->count(),
                        'total_appointments' => $clinic->appointments()->count(),
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'clinics' => $clinics,
                'current_clinic_id' => $user->current_clinic_id ?: session('current_clinic_id')
            ]
        ]);
    }
}
