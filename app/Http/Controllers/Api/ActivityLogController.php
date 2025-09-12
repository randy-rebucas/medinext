<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ActivityLog",
 *     type="object",
 *     title="Activity Log",
 *     description="Activity log model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="module", type="string", example="patients"),
 *     @OA\Property(property="action", type="string", example="created"),
 *     @OA\Property(property="description", type="string", example="Created new patient record for John Doe"),
 *     @OA\Property(property="old_values", type="object", nullable=true),
 *     @OA\Property(property="new_values", type="object", nullable=true),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */

class ActivityLogController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs",
     *     summary="Get all activity logs",
     *     description="Retrieve a paginated list of all activity logs in the system",
     *     tags={"Activity Logs"},
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
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
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
     *         @OA\Schema(type="string", example="created")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Activity logs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ActivityLog")
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
            $this->requirePermission('activity_logs.view');

            $currentClinic = $this->getCurrentClinic();
            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $query = ActivityLog::with('user')
                ->where('clinic_id', $currentClinic->id);

            // Apply filters
            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            if ($request->has('module')) {
                $query->where('module', $request->get('module'));
            }

            if ($request->has('action')) {
                $query->where('action', $request->get('action'));
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            $activityLogs = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($activityLogs, 'Activity logs retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs/{id}",
     *     summary="Get activity log by ID",
     *     description="Retrieve a specific activity log by its ID",
     *     tags={"Activity Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Activity Log ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity log retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Activity log retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="activity_log", ref="#/components/schemas/ActivityLog")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity log not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $activityLog = ActivityLog::with('user')->findOrFail($id);

            return $this->successResponse(['activity_log' => $activityLog], 'Activity log retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs/user/{user_id}",
     *     summary="Get user activity logs",
     *     description="Retrieve activity logs for a specific user",
     *     tags={"Activity Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user_id",
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
     *         @OA\Schema(type="string", example="created")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User activity logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User activity logs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="activity_logs",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ActivityLog")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     */
    public function userActivity($userId, Request $request): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);

            $query = $user->activityLogs();

            // Apply filters
            if ($request->has('module')) {
                $query->where('module', $request->get('module'));
            }

            if ($request->has('action')) {
                $query->where('action', $request->get('action'));
            }

            $activityLogs = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($activityLogs, 'User activity logs retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs/module/{module}",
     *     summary="Get module activity logs",
     *     description="Retrieve activity logs for a specific module",
     *     tags={"Activity Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="module",
     *         in="path",
     *         description="Module name",
     *         required=true,
     *         @OA\Schema(type="string", example="patients")
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
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action",
     *         required=false,
     *         @OA\Schema(type="string", example="created")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Module activity logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Module activity logs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="activity_logs",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ActivityLog")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     )
     * )
     */
    public function moduleActivity($module, Request $request): JsonResponse
    {
        try {
            $query = ActivityLog::with('user')->where('module', $module);

            // Apply filters
            if ($request->has('action')) {
                $query->where('action', $request->get('action'));
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            $activityLogs = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($activityLogs, 'Module activity logs retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs/export",
     *     summary="Export activity logs",
     *     description="Export activity logs to CSV or Excel format",
     *     tags={"Activity Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Export format",
     *         required=false,
     *         @OA\Schema(type="string", enum={"csv", "excel"}, example="csv")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="module",
     *         in="query",
     *         description="Filter by module",
     *         required=false,
     *         @OA\Schema(type="string", example="patients")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity logs exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Activity logs exported successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="download_url", type="string", example="/downloads/activity-logs-2024-01-15.csv"),
     *                 @OA\Property(property="file_name", type="string", example="activity-logs-2024-01-15.csv"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-16T10:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'format' => 'in:csv,excel',
                'user_id' => 'integer|exists:users,id',
                'module' => 'string',
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $format = $request->get('format', 'csv');

            // Build query with filters
            $query = ActivityLog::with('user');

            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            if ($request->has('module')) {
                $query->where('module', $request->get('module'));
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            $activityLogs = $query->orderBy('created_at', 'desc')->get();

            // Generate filename
            $timestamp = now()->format('Y-m-d-H-i-s');
            $filename = "activity-logs-{$timestamp}.{$format}";

            // TODO: Implement actual export logic
            // This would typically use Laravel Excel or similar package
            $downloadUrl = "/downloads/{$filename}";
            $expiresAt = now()->addHours(24);

            return $this->successResponse([
                'download_url' => $downloadUrl,
                'file_name' => $filename,
                'expires_at' => $expiresAt->toISOString(),
                'total_records' => $activityLogs->count()
            ], 'Activity logs exported successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
