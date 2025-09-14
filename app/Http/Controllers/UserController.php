<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display the users management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Users Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/users', [
                'users' => [],
                'roles' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get users for this clinic
        $users = $this->getUsers($clinicId);

        // Get roles for dropdown
        $roles = $this->getRoles();

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/users', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'password' => 'required|string|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'password_confirmation' => 'required|string|same:password',
            'is_active' => 'nullable|boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ];

        $messages = [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number can only contain numbers, spaces, hyphens, parentheses, and plus sign.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password_confirmation.same' => 'Password confirmation does not match.',
            'roles.required' => 'At least one role must be selected.',
            'roles.*.exists' => 'Selected role does not exist.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->logWebRequest('Create User', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated user creation attempt');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userClinicRole = $this->getUserClinicRole($request);

            if (!$userClinicRole) {
                $this->logSecurityEvent('Unauthorized clinic access attempt', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;

            // Create user
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->is_active ?? true,
                'email_verified_at' => now(),
            ]);

            // Assign roles to user
            $newUser->roles()->sync($request->roles);

            // Create user clinic role
            $newUser->userClinicRoles()->create([
                'clinic_id' => $clinicId,
                'role_id' => $request->roles[0], // Primary role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $this->getUser($newUser->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'UserController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'password' => 'nullable|string|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'password_confirmation' => 'nullable|string|same:password',
            'is_active' => 'nullable|boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ];

        $messages = [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone number can only contain numbers, spaces, hyphens, parentheses, and plus sign.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password_confirmation.same' => 'Password confirmation does not match.',
            'roles.required' => 'At least one role must be selected.',
            'roles.*.exists' => 'Selected role does not exist.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            // Update user
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_active' => $request->is_active ?? true,
            ];

            if ($request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Update roles
            $user->roles()->sync($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $this->getUser($user->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'UserController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deletion of superadmin users
            if ($user->hasRole('superadmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete superadmin users'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'UserController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user details
     */
    public function show($id)
    {
        try {
            $user = $this->getUser($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'UserController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deactivating superadmin users
            if ($user->hasRole('superadmin') && $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate superadmin users'
                ], 403);
            }

            $user->update(['is_active' => !$user->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'user' => $this->getUser($user->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'UserController::toggleStatus');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status. Please try again.'
            ], 500);
        }
    }

    /**
     * Get users for a clinic with caching
     */
    private function getUsers($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('users', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return User::whereHas('userClinicRoles', function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->with(['roles:id,name', 'userClinicRoles.role:id,name'])
            ->select('id', 'name', 'email', 'phone', 'is_active', 'email_verified_at', 'created_at', 'updated_at')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_active' => $user->is_active,
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'email_verified' => !is_null($user->email_verified_at),
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    }),
                    'primary_role' => $user->userClinicRoles->first()?->role?->name ?? 'No Role',
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                ];
            });
        });
    }

    /**
     * Get roles for dropdown
     */
    private function getRoles()
    {
        return Role::select('id', 'name', 'display_name')
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                ];
            });
    }

    /**
     * Get a single user
     */
    private function getUser($userId)
    {
        $user = User::with(['roles', 'userClinicRoles.role'])->findOrFail($userId);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'status' => $user->is_active ? 'Active' : 'Inactive',
            'email_verified' => !is_null($user->email_verified_at),
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                ];
            }),
            'primary_role' => $user->userClinicRoles->first()?->role?->name ?? 'No Role',
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_users', 'view_users', 'create_users', 'edit_users', 'delete_users',
                'manage_roles', 'view_roles', 'create_roles', 'edit_roles', 'delete_roles'
            ],
            'admin' => [
                'manage_users', 'view_users', 'create_users', 'edit_users', 'delete_users'
            ],
            'doctor' => [
                'view_users'
            ],
            'receptionist' => [
                'view_users', 'create_users'
            ],
            'patient' => [
                'view_users'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
