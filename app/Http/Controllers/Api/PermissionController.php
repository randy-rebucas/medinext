<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;



class PermissionController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/permissions",
     *     summary="Get all permissions",
     *     description="Retrieve a paginated list of all permissions in the system",
     *     tags={"Permission Management"},
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
     *         name="module",
     *         in="query",
     *         description="Filter by module",
     *         required=false,
     *         @OA\Schema(type="string", example="patients")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action",
     *         required=false,
     *         @OA\Schema(type="string", example="manage")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permissions retrieved successfully"),
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
            $query = Permission::query();

            // Apply filters
            if ($request->has('module')) {
                $query->where('module', $request->get('module'));
            }

            if ($request->has('action')) {
                $query->where('action', $request->get('action'));
            }

            $permissions = $query->orderBy('module')
                ->orderBy('action')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($permissions, 'Permissions retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/permissions",
     *     summary="Create a new permission",
     *     description="Create a new permission in the system",
     *     tags={"Permission Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "module", "action", "description"},
     *             @OA\Property(property="name", type="string", example="manage_patients"),
     *             @OA\Property(property="module", type="string", example="patients"),
     *             @OA\Property(property="action", type="string", example="manage"),
     *             @OA\Property(property="description", type="string", example="Manage patient records")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="permission", type="object")
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
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:permissions',
                'module' => 'required|string|max:255',
                'action' => 'required|string|max:255',
                'description' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $permission = Permission::create([
                'name' => $request->name,
                'module' => $request->module,
                'action' => $request->action,
                'description' => $request->description,
            ]);

            return $this->successResponse(['permission' => $permission], 'Permission created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/permissions/{id}",
     *     summary="Get permission by ID",
     *     description="Retrieve a specific permission by its ID",
     *     tags={"Permission Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="permission", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);

            return $this->successResponse(['permission' => $permission], 'Permission retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/permissions/{id}",
     *     summary="Update permission",
     *     description="Update an existing permission's information",
     *     tags={"Permission Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="updated_permission_name"),
     *             @OA\Property(property="module", type="string", example="patients"),
     *             @OA\Property(property="action", type="string", example="manage"),
     *             @OA\Property(property="description", type="string", example="Updated permission description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="permission", type="object")
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
     *         description="Permission not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:permissions,name,' . $permission->id,
                'module' => 'sometimes|required|string|max:255',
                'action' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $permission->update($request->only(['name', 'module', 'action', 'description']));

            return $this->successResponse(['permission' => $permission], 'Permission updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/permissions/{id}",
     *     summary="Delete permission",
     *     description="Delete a permission from the system",
     *     tags={"Permission Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found",
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
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return $this->successResponse(null, 'Permission deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/permissions/modules",
     *     summary="Get permission modules",
     *     description="Retrieve all unique permission modules in the system",
     *     tags={"Permission Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permission modules retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission modules retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="modules",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="module", type="string", example="patients"),
     *                         @OA\Property(property="count", type="integer", example=5),
     *                         @OA\Property(
     *                             property="permissions",
     *                             type="array",
     *                             @OA\Items(type="object")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function modules(): JsonResponse
    {
        try {
            $modules = Permission::select('module')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('module')
                ->orderBy('module')
                ->get()
                ->map(function ($module) {
                    return [
                        'module' => $module->module,
                        'count' => $module->count,
                        'permissions' => Permission::where('module', $module->module)
                            ->orderBy('action')
                            ->get()
                    ];
                });

            return $this->successResponse(['modules' => $modules], 'Permission modules retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
