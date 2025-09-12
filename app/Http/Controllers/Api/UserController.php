<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Dr. John Smith"),
 *     @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="clinics",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Clinic")
 *     ),
 *     @OA\Property(
 *         property="roles",
 *         type="array",
 *         @OA\Items(type="object")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     title="Role",
 *     description="Role model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="doctor"),
 *     @OA\Property(property="description", type="string", example="Medical doctor with full patient access"),
 *     @OA\Property(property="is_system_role", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         @OA\Items(type="object")
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *     title="Permission",
 *     description="Permission model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="manage_patients"),
 *     @OA\Property(property="module", type="string", example="patients"),
 *     @OA\Property(property="action", type="string", example="manage"),
 *     @OA\Property(property="description", type="string", example="Manage patient records"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Clinic",
 *     type="object",
 *     title="Clinic",
 *     description="Clinic model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Downtown Medical Center"),
 *     @OA\Property(property="slug", type="string", example="downtown-medical-center"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, State 12345"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="email", type="string", format="email", example="info@downtownmedical.com"),
 *     @OA\Property(property="website", type="string", example="https://downtownmedical.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Validation Error",
 *     description="Validation error response",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Validation failed"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="email",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The email field is required", "The email must be a valid email address"}
 *         ),
 *         @OA\Property(
 *             property="password",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The password field is required", "The password must be at least 8 characters"}
 *         )
 *     )
 * )
 */

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get all users",
     *     description="Retrieve a paginated list of all users in the system",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for name or email",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role",
     *         required=false,
     *         @OA\Schema(type="string", example="doctor")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"}, example="active")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('users.view');

            $query = User::with(['roles', 'clinics']);

            // Apply clinic filtering for non-superadmin users
            if (!$this->hasRole('superadmin')) {
                $currentClinic = $this->getCurrentClinic();
                if ($currentClinic) {
                    $query->whereHas('userClinicRoles', function ($q) use ($currentClinic) {
                        $q->where('clinic_id', $currentClinic->id);
                    });
                }
            }

            // Apply filters
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->get('role'));
                });
            }

            if ($request->has('status')) {
                $query->where('is_active', $request->get('status') === 'active');
            }

            if ($request->has('clinic_id')) {
                $clinicId = $request->get('clinic_id');
                $this->requireClinicAccess($clinicId);
                $query->whereHas('userClinicRoles', function ($q) use ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                });
            }

            $users = $query->paginate($request->get('per_page', 15));

            return $this->successResponse($users, 'Users retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Create a new user",
     *     description="Create a new user account with role assignment",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="clinic_id", type="integer", example=1),
     *             @OA\Property(property="role_id", type="integer", example=2),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('users.create');

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'clinic_id' => 'required|integer|exists:clinics,id',
                'role_id' => 'required|integer|exists:roles,id',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Ensure user has access to the clinic they're creating a user for
            $this->requireClinicAccess($request->clinic_id);

            // Check if user can assign the specified role
            $role = Role::findOrFail($request->role_id);
            if (!$this->hasRole('superadmin') && $role->name === 'superadmin') {
                throw new \Illuminate\Auth\Access\AuthorizationException('Cannot create superadmin users');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'is_active' => $request->get('is_active', true),
            ]);

            // Assign clinic and role
            $user->clinics()->attach($request->clinic_id, [
                'role_id' => $request->role_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->load(['roles', 'clinics']);

            return $this->successResponse(['user' => $user], 'User created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get user by ID",
     *     description="Retrieve a specific user by their ID",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('users.view');

            $user = User::with(['roles', 'clinics', 'permissions'])->findOrFail($id);

            // Check if user can view this specific user (clinic access)
            if (!$this->hasRole('superadmin')) {
                $currentClinic = $this->getCurrentClinic();
                if ($currentClinic && !$user->userClinicRoles()->where('clinic_id', $currentClinic->id)->exists()) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('No access to view this user');
                }
            }

            return $this->successResponse(['user' => $user], 'User retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     summary="Update user",
     *     description="Update an existing user's information",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'phone' => 'nullable|string|max:20',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->update($request->only(['name', 'email', 'phone', 'is_active']));
            $user->load(['roles', 'clinics']);

            return $this->successResponse(['user' => $user], 'User updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     summary="Delete user",
     *     description="Soft delete a user (deactivate their account)",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Soft delete - deactivate user
            $user->update(['is_active' => false]);

            return $this->successResponse(null, 'User deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}/permissions",
     *     summary="Get user permissions",
     *     description="Retrieve all permissions assigned to a specific user",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User permissions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function permissions($id): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('users.view');

            $user = User::findOrFail($id);

            // Check if user can view this specific user's permissions (clinic access)
            if (!$this->hasRole('superadmin')) {
                $currentClinic = $this->getCurrentClinic();
                if ($currentClinic && !$user->userClinicRoles()->where('clinic_id', $currentClinic->id)->exists()) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('No access to view this user\'s permissions');
                }
            }

            $permissions = $user->getAllPermissions();

            return $this->successResponse(['permissions' => $permissions], 'User permissions retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{id}/permissions",
     *     summary="Assign permissions to user",
     *     description="Assign specific permissions to a user",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permissions assigned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function assignPermissions(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->permissions()->sync($request->permissions);

            return $this->successResponse(null, 'Permissions assigned successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}/roles",
     *     summary="Get user roles",
     *     description="Retrieve all roles assigned to a specific user",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User roles retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function roles($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $roles = $user->roles;

            return $this->successResponse(['roles' => $roles], 'User roles retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{id}/roles",
     *     summary="Assign roles to user",
     *     description="Assign specific roles to a user",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles assigned successfully")
     *         )
     *     )
     * )
     */
    public function assignRoles(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'roles' => 'required|array',
                'roles.*' => 'integer|exists:roles,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->roles()->sync($request->roles);

            return $this->successResponse(null, 'Roles assigned successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}/activity",
     *     summary="Get user activity",
     *     description="Retrieve activity log for a specific user",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User activity retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User activity retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="activity",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ActivityLog")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     */
    public function activity($id, Request $request): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $activity = $user->activityLogs()
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($activity, 'User activity retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{id}/activate",
     *     summary="Activate user",
     *     description="Activate a user account",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User activated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User activated successfully")
     *         )
     *     )
     * )
     */
    public function activate($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['is_active' => true]);

            return $this->successResponse(null, 'User activated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{id}/deactivate",
     *     summary="Deactivate user",
     *     description="Deactivate a user account",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deactivated successfully")
     *         )
     *     )
     * )
     */
    public function deactivate($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['is_active' => false]);

            return $this->successResponse(null, 'User deactivated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{id}/reset-password",
     *     summary="Reset user password",
     *     description="Reset a user's password to a new temporary password",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="send_email", type="boolean", example=true, description="Send password reset email to user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8',
                'send_email' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Send email notification if requested
            if ($request->get('send_email', false)) {
                // TODO: Implement email notification
            }

            return $this->successResponse(null, 'Password reset successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/search/users",
     *     summary="Search users",
     *     description="Search users by name or email",
     *     tags={"Search"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            $limit = $request->get('limit', 10);

            if (empty($query)) {
                return $this->errorResponse('Search query is required', 400);
            }

            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit($limit)
                ->get();

            return $this->successResponse(['users' => $users], 'Search results retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
