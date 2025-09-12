<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *     title="Notification",
 *     description="Notification model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="New Appointment"),
 *     @OA\Property(property="message", type="string", example="You have a new appointment scheduled for tomorrow"),
 *     @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success"}, example="info"),
 *     @OA\Property(property="is_read", type="boolean", example=false),
 *     @OA\Property(property="data", type="object", example={"appointment_id": 1, "patient_name": "John Doe"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */



class NotificationController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     summary="Get all notifications",
     *     description="Retrieve a paginated list of notifications for the authenticated user",
     *     tags={"Notifications"},
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
     *         name="type",
     *         in="query",
     *         description="Filter by notification type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"info", "warning", "error", "success"}, example="info")
     *     ),
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Filter by read status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notifications retrieved successfully"),
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
            $user = $request->user();
            $query = $user->notifications();

            // Apply filters
            if ($request->has('type')) {
                $query->where('type', $request->get('type'));
            }

            if ($request->has('is_read')) {
                $query->where('is_read', $request->get('is_read'));
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($notifications, 'Notifications retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/{id}",
     *     summary="Get notification by ID",
     *     description="Retrieve a specific notification by its ID",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="notification", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();
            $notification = $user->notifications()->findOrFail($id);

            return $this->successResponse(['notification' => $notification], 'Notification retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications",
     *     summary="Create a new notification",
     *     description="Create a new notification for a user",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "title", "message", "type"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="New Appointment"),
     *             @OA\Property(property="message", type="string", example="You have a new appointment scheduled for tomorrow"),
     *             @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success"}, example="info"),
     *             @OA\Property(property="data", type="object", example={"appointment_id": 1, "patient_name": "John Doe"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Notification created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="notification", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'type' => 'required|string|in:info,warning,error,success',
                'data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $notification = Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'data' => $request->get('data', []),
                'is_read' => false,
            ]);

            return $this->successResponse(['notification' => $notification], 'Notification created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/notifications/{id}",
     *     summary="Update notification",
     *     description="Update an existing notification",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Notification Title"),
     *             @OA\Property(property="message", type="string", example="Updated notification message"),
     *             @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success"}, example="warning"),
     *             @OA\Property(property="data", type="object", example={"appointment_id": 1, "patient_name": "John Doe"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="notification", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = request()->user();
            $notification = $user->notifications()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'message' => 'sometimes|required|string|max:1000',
                'type' => 'sometimes|required|string|in:info,warning,error,success',
                'data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $notification->update($request->only(['title', 'message', 'type', 'data']));

            return $this->successResponse(['notification' => $notification], 'Notification updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notifications/{id}",
     *     summary="Delete notification",
     *     description="Delete a notification",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = request()->user();
            $notification = $user->notifications()->findOrFail($id);
            $notification->delete();

            return $this->successResponse(null, 'Notification deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/mark-read",
     *     summary="Mark notification as read",
     *     description="Mark a specific notification as read",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notification_id"},
     *             @OA\Property(property="notification_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification marked as read successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|integer|exists:notifications,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = request()->user();
            $notification = $user->notifications()->findOrFail($request->notification_id);
            $notification->update(['is_read' => true]);

            return $this->successResponse(null, 'Notification marked as read successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/mark-all-read",
     *     summary="Mark all notifications as read",
     *     description="Mark all notifications for the authenticated user as read",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All notifications marked as read successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="updated_count", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = request()->user();
            $updatedCount = $user->notifications()->where('is_read', false)->update(['is_read' => true]);

            return $this->successResponse(['updated_count' => $updatedCount], 'All notifications marked as read successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notifications/unread",
     *     summary="Get unread notifications",
     *     description="Retrieve all unread notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of notifications to return",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unread notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Unread notifications retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="notifications",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="unread_count", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function unread(Request $request): JsonResponse
    {
        try {
            $user = request()->user();
            $limit = $request->get('limit', 10);

            $notifications = $user->notifications()
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $unreadCount = $user->notifications()->where('is_read', false)->count();

            return $this->successResponse([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ], 'Unread notifications retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/send",
     *     summary="Send notification",
     *     description="Send a notification to one or more users",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_ids", "title", "message", "type"},
     *             @OA\Property(
     *                 property="user_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             ),
     *             @OA\Property(property="title", type="string", example="System Maintenance"),
     *             @OA\Property(property="message", type="string", example="The system will be under maintenance from 2 AM to 4 AM"),
     *             @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success"}, example="warning"),
     *             @OA\Property(property="data", type="object", example={"maintenance_start": "2024-01-15T02:00:00Z", "maintenance_end": "2024-01-15T04:00:00Z"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notifications sent successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="sent_count", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function send(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id',
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'type' => 'required|string|in:info,warning,error,success',
                'data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $notifications = [];
            foreach ($request->user_ids as $userId) {
                $notifications[] = [
                    'user_id' => $userId,
                    'title' => $request->title,
                    'message' => $request->message,
                    'type' => $request->type,
                    'data' => $request->get('data', []),
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Notification::insert($notifications);

            return $this->successResponse(['sent_count' => count($notifications)], 'Notifications sent successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
