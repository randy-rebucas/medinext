<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;



class RoleController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     summary="Get all roles",
     *     description="Retrieve a paginated list of all roles in the system",
     *     tags={"Role Management"},
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
     *         description="Search term for role name or description",
     *         required=false,
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Parameter(
     *         name="system_role",
     *         in="query",
     *         description="Filter by system role status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('roles.view');

            $query = Role::with(['permissions'])->withCount('userClinicRoles');

            // Apply filters
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('system_role')) {
                $query->where('is_system_role', $request->get('system_role'));
            }

            // Filter out system roles for non-superadmin users
            if (!$this->hasRole('superadmin')) {
                $query->where('is_system_role', false);
            }

            $roles = $query->paginate($request->get('per_page', 15));

            return $this->successResponse($roles, 'Roles retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/roles",
     *     summary="Create a new role",
     *     description="Create a new role with optional permissions",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="custom_role"),
     *             @OA\Property(property="description", type="string", example="Custom role for specific clinic needs"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3},
     *                 description="Array of permission IDs to assign to this role"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="role", type="object")
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
            $this->requirePermission('roles.create');

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles',
                'description' => 'required|string|max:1000',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Prevent non-superadmin users from creating system roles
            if ($request->has('is_system_role') && $request->is_system_role && !$this->hasRole('superadmin')) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Cannot create system roles');
            }

            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_system_role' => $request->get('is_system_role', false),
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->attach($request->permissions);
            }

            $role->load('permissions');

            return $this->successResponse(['role' => $role], 'Role created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}",
     *     summary="Get role by ID",
     *     description="Retrieve a specific role by its ID with permissions",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="role", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $role = Role::with(['permissions', 'userClinicRoles.user', 'userClinicRoles.clinic'])
                ->withCount('userClinicRoles')
                ->findOrFail($id);

            return $this->successResponse(['role' => $role], 'Role retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/roles/{id}",
     *     summary="Update role",
     *     description="Update an existing role's information (system roles cannot be modified)",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="updated_role_name"),
     *             @OA\Property(property="description", type="string", example="Updated role description"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3, 4}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="role", type="object")
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
     *         description="Cannot modify system role",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            if ($role->is_system_role) {
                return $this->errorResponse('System roles cannot be modified', 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles')->ignore($role->id)
                ],
                'description' => 'required|string|max:1000',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $role->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // Sync permissions
            $role->permissions()->sync($request->get('permissions', []));
            $role->load('permissions');

            return $this->successResponse(['role' => $role], 'Role updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/roles/{id}",
     *     summary="Delete role",
     *     description="Delete a role (system roles and roles in use cannot be deleted)",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cannot delete system role or role in use",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            if ($role->is_system_role) {
                return $this->errorResponse('System roles cannot be deleted', 403);
            }

            // Check if role is in use
            if ($role->userClinicRoles()->count() > 0) {
                return $this->errorResponse('Cannot delete role that is currently assigned to users', 403);
            }

            $role->delete();

            return $this->successResponse(null, 'Role deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}/permissions",
     *     summary="Get role permissions",
     *     description="Retrieve all permissions assigned to a specific role",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role permissions retrieved successfully"),
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
     *     )
     * )
     */
    public function permissions($id): JsonResponse
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            return $this->successResponse(['permissions' => $role->permissions], 'Role permissions retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/roles/{id}/permissions",
     *     summary="Assign permissions to role",
     *     description="Assign specific permissions to a role (system roles cannot be modified)",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
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
     *                 example={1, 2, 3, 4}
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
     *         response=403,
     *         description="Cannot modify system role permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function assignPermissions(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            if ($role->is_system_role) {
                return $this->errorResponse('System role permissions cannot be modified', 403);
            }

            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $role->permissions()->sync($request->permissions);

            return $this->successResponse(null, 'Permissions assigned successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}/users",
     *     summary="Get role users",
     *     description="Retrieve all users assigned to a specific role",
     *     tags={"Role Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
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
     *         description="Role users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role users retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     */
    public function users($id, Request $request): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $users = $role->users()
                ->with(['clinics', 'roles'])
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($users, 'Role users retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
