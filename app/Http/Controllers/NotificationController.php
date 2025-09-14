<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display the notifications management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Notifications Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/notifications', [
                'notifications' => [],
                'users' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get notifications for this clinic
        $notifications = $this->getNotifications($clinicId);

        // Get users for dropdown
        $users = $this->getUsers($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/notifications', [
            'notifications' => $notifications,
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created notification
     */
    public function store(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|string|in:info,warning,error,success',
            'data' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ];

        $messages = [
            'user_id.exists' => 'Selected user does not exist.',
            'title.required' => 'Notification title is required.',
            'message.required' => 'Notification message is required.',
            'type.in' => 'Invalid notification type.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];

        try {
            $validatedData = $this->validateAndSanitize($request, $rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $this->logWebRequest('Create Notification', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated notification creation attempt');
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

            // Create notification
            $notification = Notification::create([
                'clinic_id' => $clinicId,
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
                'message' => $validatedData['message'],
                'type' => $validatedData['type'],
                'data' => $validatedData['data'] ?? [],
                'scheduled_at' => $validatedData['scheduled_at'] ?? null,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'notification' => $this->getNotification($notification->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'NotificationController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified notification
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|string|in:info,warning,error,success',
            'data' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ];

        $messages = [
            'user_id.exists' => 'Selected user does not exist.',
            'title.required' => 'Notification title is required.',
            'message.required' => 'Notification message is required.',
            'type.in' => 'Invalid notification type.',
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
            $notification = Notification::findOrFail($id);

            // Update notification
            $notification->update([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'data' => $request->data ?? [],
                'scheduled_at' => $request->scheduled_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification updated successfully',
                'notification' => $this->getNotification($notification->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'NotificationController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified notification
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'NotificationController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification. Please try again.'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'NotificationController::markAsRead');
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read. Please try again.'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'NotificationController::markAllAsRead');
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user's notifications
     */
    public function getUserNotifications(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'data' => $notification->data,
                        'is_read' => !is_null($notification->read_at),
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'NotificationController::getUserNotifications');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications. Please try again.'
            ], 500);
        }
    }

    /**
     * Get notifications for a clinic with caching
     */
    private function getNotifications($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('notifications', $clinicId);
        
        return $this->remember($cacheKey, 15, function () use ($clinicId) {
            return Notification::where('clinic_id', $clinicId)
                ->with(['user:id,name,email'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'data' => $notification->data,
                        'user_name' => $notification->user->name,
                        'user_email' => $notification->user->email,
                        'user_id' => $notification->user_id,
                        'is_read' => !is_null($notification->read_at),
                        'scheduled_at' => $notification->scheduled_at,
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
                    ];
                });
        });
    }

    /**
     * Get users for dropdown
     */
    private function getUsers($clinicId)
    {
        return User::whereHas('userClinicRoles', function ($query) use ($clinicId) {
            $query->where('clinic_id', $clinicId);
        })
        ->select('id', 'name', 'email')
        ->orderBy('name')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });
    }

    /**
     * Get a single notification
     */
    private function getNotification($notificationId)
    {
        $notification = Notification::with(['user'])->findOrFail($notificationId);

        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'data' => $notification->data,
            'user' => [
                'id' => $notification->user->id,
                'name' => $notification->user->name,
                'email' => $notification->user->email,
            ],
            'is_read' => !is_null($notification->read_at),
            'scheduled_at' => $notification->scheduled_at,
            'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
            'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_notifications', 'view_notifications', 'create_notifications',
                'edit_notifications', 'delete_notifications'
            ],
            'admin' => [
                'manage_notifications', 'view_notifications', 'create_notifications',
                'edit_notifications', 'delete_notifications'
            ],
            'doctor' => [
                'view_notifications', 'create_notifications'
            ],
            'receptionist' => [
                'view_notifications', 'create_notifications'
            ],
            'patient' => [
                'view_notifications'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
