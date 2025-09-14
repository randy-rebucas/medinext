<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Room;
use App\Models\Clinic;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display the room management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Room Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/rooms', [
                'rooms' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get rooms for this clinic
        $rooms = $this->getRooms($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/rooms', [
            'rooms' => $rooms,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.]+$/',
            'room_number' => 'required|string|max:50|regex:/^[a-zA-Z0-9\s\-\'\.]+$/',
            'room_type' => 'required|string|in:consultation,examination,procedure,waiting,office',
            'capacity' => 'required|integer|min:1|max:100',
            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'name.regex' => 'Room name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'room_number.regex' => 'Room number can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'room_type.in' => 'Invalid room type.',
            'capacity.min' => 'Room capacity must be at least 1.',
            'capacity.max' => 'Room capacity cannot exceed 100.',
            'equipment.*.string' => 'Equipment items must be strings.',
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
            $this->logWebRequest('Create Room', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated room creation attempt');
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

            // Check for duplicate room number in clinic
            $existingRoom = Room::where('clinic_id', $clinicId)
                ->where('room_number', $request->room_number)
                ->first();

            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room number already exists in this clinic'
                ], 422);
            }

            // Create room
            $room = Room::create([
                'clinic_id' => $clinicId,
                'name' => $request->name,
                'room_number' => $request->room_number,
                'room_type' => $request->room_type,
                'capacity' => $request->capacity,
                'equipment' => $request->equipment ?? [],
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'room' => $this->getRoom($room->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.]+$/',
            'room_number' => 'required|string|max:50|regex:/^[a-zA-Z0-9\s\-\'\.]+$/',
            'room_type' => 'required|string|in:consultation,examination,procedure,waiting,office',
            'capacity' => 'required|integer|min:1|max:100',
            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [
            'name.regex' => 'Room name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'room_number.regex' => 'Room number can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'room_type.in' => 'Invalid room type.',
            'capacity.min' => 'Room capacity must be at least 1.',
            'capacity.max' => 'Room capacity cannot exceed 100.',
            'equipment.*.string' => 'Equipment items must be strings.',
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
            $room = Room::findOrFail($id);

            // Check for duplicate room number in clinic (excluding current room)
            $existingRoom = Room::where('clinic_id', $room->clinic_id)
                ->where('room_number', $request->room_number)
                ->where('id', '!=', $id)
                ->first();

            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room number already exists in this clinic'
                ], 422);
            }

            // Update room
            $room->update([
                'name' => $request->name,
                'room_number' => $request->room_number,
                'room_type' => $request->room_type,
                'capacity' => $request->capacity,
                'equipment' => $request->equipment ?? [],
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'room' => $this->getRoom($room->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified room
     */
    public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room. Please try again.'
            ], 500);
        }
    }

    /**
     * Get room details
     */
    public function show($id)
    {
        try {
            $room = $this->getRoom($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Room retrieved successfully',
                'room' => $room
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve room. Please try again.'
            ], 500);
        }
    }

    /**
     * Get rooms for a clinic with caching
     */
    private function getRooms($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('rooms', $clinicId);
        
        return $this->remember($cacheKey, 60, function () use ($clinicId) {
            return Room::where('clinic_id', $clinicId)
                ->orderBy('room_number')
                ->get()
                ->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'room_number' => $room->room_number,
                        'room_type' => $room->room_type,
                        'capacity' => $room->capacity,
                        'equipment' => $room->equipment,
                        'is_active' => $room->is_active,
                        'status' => $room->is_active ? 'Active' : 'Inactive',
                        'created_at' => $room->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
        });
    }

    /**
     * Get a single room
     */
    private function getRoom($roomId)
    {
        $room = Room::findOrFail($roomId);

        return [
            'id' => $room->id,
            'name' => $room->name,
            'room_number' => $room->room_number,
            'room_type' => $room->room_type,
            'capacity' => $room->capacity,
            'equipment' => $room->equipment,
            'is_active' => $room->is_active,
            'status' => $room->is_active ? 'Active' : 'Inactive',
            'created_at' => $room->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_rooms', 'view_rooms', 'create_rooms', 'edit_rooms', 'delete_rooms'
            ],
            'admin' => [
                'manage_rooms', 'view_rooms', 'create_rooms', 'edit_rooms', 'delete_rooms'
            ],
            'doctor' => [
                'view_rooms'
            ],
            'receptionist' => [
                'view_rooms', 'create_rooms', 'edit_rooms'
            ],
            'patient' => [
                'view_rooms'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
