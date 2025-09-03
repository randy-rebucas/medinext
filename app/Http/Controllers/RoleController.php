<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('userClinicRoles')
            ->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_system_role' => false,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'userClinicRoles.user', 'userClinicRoles.clinic']);
        
        $permissions = $role->permissions->groupBy('module');
        
        return view('roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        if ($role->isSystemRole()) {
            return back()->with('error', 'System roles cannot be modified.');
        }

        $permissions = Permission::orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        if ($role->isSystemRole()) {
            return back()->with('error', 'System roles cannot be modified.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id),
            ],
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Sync permissions
        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        if ($role->isSystemRole()) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        // Check if role is in use
        if ($role->userClinicRoles()->count() > 0) {
            return back()->with('error', 'Cannot delete role that is currently assigned to users.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Show role assignment form
     */
    public function assign(Role $role)
    {
        $clinics = Clinic::all();
        $role->load('permissions');

        return view('roles.assign', compact('role', 'clinics'));
    }

    /**
     * Show role permissions management
     */
    public function permissions(Role $role)
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, Role $role)
    {
        if ($role->isSystemRole()) {
            return back()->with('error', 'System role permissions cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }
}
