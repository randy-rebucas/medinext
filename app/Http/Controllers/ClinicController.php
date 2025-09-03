<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\UserClinicRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClinicController extends Controller
{
    /**
     * Display a listing of clinics
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user has permission to view clinics
        if (!$user->hasAnyPermissionInClinic(['clinics.view', 'clinics.manage'], 1)) {
            abort(403, 'Insufficient permissions to view clinics.');
        }

        $clinics = Clinic::withCount(['doctors', 'patients', 'appointments'])
            ->paginate(12);

        return view('clinics.index', compact('clinics'));
    }

    /**
     * Show the form for creating a new clinic
     */
    public function create(Request $request)
    {
        $user = $request->user();

        // Check if user has permission to create clinics
        if (!$user->hasAnyPermissionInClinic(['clinics.create', 'clinics.manage'], 1)) {
            abort(403, 'Insufficient permissions to create clinics.');
        }

        return view('clinics.create');
    }

    /**
     * Store a newly created clinic
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user has permission to create clinics
        if (!$user->hasAnyPermissionInClinic(['clinics.create', 'clinics.manage'], 1)) {
            abort(403, 'Insufficient permissions to create clinics.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:clinics',
            'timezone' => 'required|string|max:100',
            'logo_url' => 'nullable|url|max:500',
            'address' => 'array',
            'address.street' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:100',
            'address.state' => 'nullable|string|max:100',
            'address.postal_code' => 'nullable|string|max:20',
            'address.country' => 'nullable|string|max:100',
            'settings' => 'array',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $clinic = Clinic::create($validated);

        // Assign the creating user as admin of the new clinic
        if ($user) {
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                UserClinicRole::create([
                    'user_id' => $user->id,
                    'clinic_id' => $clinic->id,
                    'role_id' => $adminRole->id,
                ]);
            }
        }

        return redirect()->route('clinics.show', $clinic)
            ->with('success', 'Clinic created successfully.');
    }

    /**
     * Display the specified clinic
     */
    public function show(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has access to this clinic
        if (!$user->hasAnyPermissionInClinic(['clinics.view', 'clinics.manage'], $clinic->id)) {
            abort(403, 'You do not have access to this clinic.');
        }

        $clinic->load(['doctors.user', 'appointments.patient']);

        $stats = [
            'total_doctors' => $clinic->doctors()->count(),
            'total_patients' => $clinic->patients()->count(),
            'total_appointments' => $clinic->appointments()->count(),
            'total_encounters' => $clinic->encounters()->count(),
        ];

        return view('clinics.show', compact('clinic', 'stats'));
    }

    /**
     * Show the form for editing the specified clinic
     */
    public function edit(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to edit this clinic
        if (!$user->hasAnyPermissionInClinic(['clinics.edit', 'clinics.manage'], $clinic->id)) {
            abort(403, 'Insufficient permissions to edit this clinic.');
        }

        return view('clinics.edit', compact('clinic'));
    }

    /**
     * Update the specified clinic
     */
    public function update(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to edit this clinic
        if (!$user->hasAnyPermissionInClinic(['clinics.edit', 'clinics.manage'], $clinic->id)) {
            abort(403, 'Insufficient permissions to edit this clinic.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clinics')->ignore($clinic->id),
            ],
            'timezone' => 'required|string|max:100',
            'logo_url' => 'nullable|url|max:500',
            'address' => 'array',
            'address.street' => 'nullable|string|max:255',
            'address.city' => 'nullable|string|max:100',
            'address.state' => 'nullable|string|max:100',
            'address.postal_code' => 'nullable|string|max:20',
            'address.country' => 'nullable|string|max:100',
            'settings' => 'array',
        ]);

        $clinic->update($validated);

        return redirect()->route('clinics.show', $clinic)
            ->with('success', 'Clinic updated successfully.');
    }

    /**
     * Remove the specified clinic
     */
    public function destroy(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to delete this clinic
        if (!$user->hasAnyPermissionInClinic(['clinics.delete', 'clinics.manage'], $clinic->id)) {
            abort(403, 'Insufficient permissions to delete this clinic.');
        }

        $clinic->delete();

        return redirect()->route('clinics.index')
            ->with('success', 'Clinic deleted successfully.');
    }

    /**
     * Show clinic members
     */
    public function members(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to manage clinic members
        if (!$user->hasAnyPermissionInClinic(['users.manage', 'clinics.manage'], $clinic->id)) {
            abort(403, 'Insufficient permissions to manage clinic members.');
        }

        $members = $clinic->userClinicRoles()
            ->with(['user', 'role'])
            ->get();

        $availableUsers = \App\Models\User::whereDoesntHave('userClinicRoles', function ($query) use ($clinic) {
            $query->where('clinic_id', $clinic->id);
        })->get();

        $roles = Role::all();

        return view('clinics.members', compact('clinic', 'members', 'availableUsers', 'roles'));
    }

    /**
     * Add member to clinic
     */
    public function addMember(Request $request, Clinic $clinic)
    {
        $user = $request->user();

        // Check if user has permission to manage clinic members
        if (!$user->hasAnyPermissionInClinic(['users.manage', 'clinics.manage'], $clinic->id)) {
            abort(403, 'Insufficient permissions to manage clinic members.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Check if user is already a member
        $existing = UserClinicRole::where('user_id', $validated['user_id'])
            ->where('clinic_id', $clinic->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'User is already a member of this clinic.');
        }

        UserClinicRole::create([
            'user_id' => $validated['user_id'],
            'clinic_id' => $clinic->id,
            'role_id' => $validated['role_id'],
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    /**
     * Remove member from clinic
     */
    public function removeMember(Request $request, Clinic $clinic, \App\Models\User $user)
    {
        $currentUser = $request->user();

        // Check if user has permission to manage clinic members
        if (!$currentUser->hasAnyPermissionInClinic(['users.manage', 'clinics.manage'], $clinic->id)) {
            abort(403, 'Insufficient permissions to manage clinic members.');
        }

        // Prevent removing the last admin
        $adminCount = $clinic->userClinicRoles()
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'superadmin']);
            })
            ->count();

        $userRole = $clinic->userClinicRoles()
            ->where('user_id', $user->id)
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'superadmin']);
            })
            ->first();

        if ($userRole && $adminCount <= 1) {
            return back()->with('error', 'Cannot remove the last administrator from the clinic.');
        }

        $clinic->userClinicRoles()
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Member removed successfully.');
    }
}
