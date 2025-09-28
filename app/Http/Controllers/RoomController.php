<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Room;
use App\Models\Clinic;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display the room management page or return JSON data
     */
    public function index(Request $request): Response|JsonResponse
    {
        $this->logWebRequest('Room Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access',
                    'rooms' => [],
                    'permissions' => []
                ], 403);
            }
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

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'rooms' => $rooms,
                'permissions' => $permissions
            ]);
        }

        // Return Inertia response for regular page requests
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
            'type' => 'required|string|in:Consultation,Examination,Procedure,Surgery,Recovery,Emergency',
            'capacity' => 'required|integer|min:1|max:50',
            'status' => 'required|string|in:Available,Occupied,Maintenance,Out of Service',
            'location' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:255',
            'maintenance_notes' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
        ];

        $messages = [
            'name.regex' => 'Room name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'type.in' => 'Invalid room type.',
            'capacity.required' => 'Room capacity is required.',
            'capacity.integer' => 'Room capacity must be a number.',
            'capacity.min' => 'Room capacity must be at least 1.',
            'capacity.max' => 'Room capacity cannot exceed 50.',
            'status.in' => 'Invalid room status.',
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

            // Check for duplicate room name in clinic
            $existingRoom = Room::where('clinic_id', $clinicId)
                ->where('name', $request->name)
                ->first();

            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room name already exists in this clinic'
                ], 422);
            }

            // Create room
            $room = Room::create([
                'clinic_id' => $clinicId,
                'name' => $request->name,
                'type' => $request->type,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'location' => $request->location,
                'description' => $request->description,
                'equipment' => $request->equipment ?? [],
                'maintenance_notes' => $request->maintenance_notes,
                'special_requirements' => $request->special_requirements,
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
            'type' => 'required|string|in:Consultation,Examination,Procedure,Surgery,Recovery,Emergency',
            'capacity' => 'required|integer|min:1|max:50',
            'status' => 'required|string|in:Available,Occupied,Maintenance,Out of Service',
            'location' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'equipment' => 'nullable|array',
            'equipment.*' => 'string|max:255',
            'maintenance_notes' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
        ];

        $messages = [
            'name.regex' => 'Room name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'type.in' => 'Invalid room type.',
            'capacity.required' => 'Room capacity is required.',
            'capacity.integer' => 'Room capacity must be a number.',
            'capacity.min' => 'Room capacity must be at least 1.',
            'capacity.max' => 'Room capacity cannot exceed 50.',
            'status.in' => 'Invalid room status.',
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

            // Check for duplicate room name in clinic (excluding current room)
            $existingRoom = Room::where('clinic_id', $room->clinic_id)
                ->where('name', $request->name)
                ->where('id', '!=', $id)
                ->first();

            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room name already exists in this clinic'
                ], 422);
            }

            // Update room
            $room->update([
                'name' => $request->name,
                'type' => $request->type,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'location' => $request->location,
                'description' => $request->description,
                'equipment' => $request->equipment ?? [],
                'maintenance_notes' => $request->maintenance_notes,
                'special_requirements' => $request->special_requirements,
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
            try {
                return Room::where('clinic_id', $clinicId)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($room) {
                        // Get appointments safely
                        $nextAppointment = null;
                        $currentAppointment = null;
                        
                        try {
                            $nextAppointment = $room->nextAppointment;
                            $currentAppointment = $room->currentAppointment;
                        } catch (\Exception $e) {
                            // If there's an issue with appointments, continue without them
                            \Log::warning('Error loading appointments for room ' . $room->id . ': ' . $e->getMessage());
                        }
                        
                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                            'type' => $room->type ?? 'Consultation',
                            'capacity' => $room->capacity ?? 1,
                            'status' => $room->status ?? 'Available',
                            'location' => $room->location,
                            'description' => $room->description,
                            'equipment' => $room->equipment ?? [],
                            'maintenance_notes' => $room->maintenance_notes,
                            'special_requirements' => $room->special_requirements,
                            'is_active' => $room->is_active ?? true,
                            'floor_number' => $room->floor_number,
                            'wing' => $room->wing,
                            'accessibility_features' => $room->accessibility_features ?? [],
                            'cleaning_schedule' => $room->cleaning_schedule,
                            'last_maintenance_date' => $room->last_maintenance_date?->format('Y-m-d H:i:s'),
                            'next_maintenance_date' => $room->next_maintenance_date?->format('Y-m-d H:i:s'),
                            'full_location' => $room->full_location ?? $room->location,
                            'equipment_list' => $room->equipment_list ?? '',
                            'is_available' => $room->is_available ?? true,
                            'status_color' => $room->getStatusColor() ?? 'green',
                            'type_icon' => $room->getTypeIcon() ?? 'building',
                            'nextAppointment' => $nextAppointment ? [
                                'id' => $nextAppointment->id,
                                'start_at' => $nextAppointment->start_at->format('Y-m-d H:i:s'),
                                'end_at' => $nextAppointment->end_at->format('Y-m-d H:i:s'),
                                'patient_name' => $nextAppointment->patient?->name ?? 'Unknown',
                                'doctor_name' => $nextAppointment->doctor?->name ?? 'Unknown',
                                'type' => $nextAppointment->appointment_type ?? 'consultation',
                                'status' => $nextAppointment->status,
                            ] : null,
                            'currentAppointment' => $currentAppointment ? [
                                'id' => $currentAppointment->id,
                                'start_at' => $currentAppointment->start_at->format('Y-m-d H:i:s'),
                                'end_at' => $currentAppointment->end_at->format('Y-m-d H:i:s'),
                                'patient_name' => $currentAppointment->patient?->name ?? 'Unknown',
                                'doctor_name' => $currentAppointment->doctor?->name ?? 'Unknown',
                                'type' => $currentAppointment->appointment_type ?? 'consultation',
                                'status' => $currentAppointment->status,
                            ] : null,
                            'doctor' => $currentAppointment?->doctor?->name ?? $nextAppointment?->doctor?->name ?? null,
                            'created_at' => $room->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
                        ];
                    });
            } catch (\Exception $e) {
                \Log::error('Error loading rooms for clinic ' . $clinicId . ': ' . $e->getMessage());
                return collect([]);
            }
        });
    }

    /**
     * Get a single room
     */
    private function getRoom($roomId)
    {
        $room = Room::findOrFail($roomId);

        // Get appointments safely
        $nextAppointment = null;
        $currentAppointment = null;
        
        try {
            $nextAppointment = $room->nextAppointment;
            $currentAppointment = $room->currentAppointment;
        } catch (\Exception $e) {
            \Log::warning('Error loading appointments for room ' . $room->id . ': ' . $e->getMessage());
        }

        return [
            'id' => $room->id,
            'name' => $room->name,
            'type' => $room->type ?? 'Consultation',
            'capacity' => $room->capacity ?? 1,
            'status' => $room->status ?? 'Available',
            'location' => $room->location,
            'description' => $room->description,
            'equipment' => $room->equipment ?? [],
            'maintenance_notes' => $room->maintenance_notes,
            'special_requirements' => $room->special_requirements,
            'is_active' => $room->is_active ?? true,
            'floor_number' => $room->floor_number,
            'wing' => $room->wing,
            'accessibility_features' => $room->accessibility_features ?? [],
            'cleaning_schedule' => $room->cleaning_schedule,
            'last_maintenance_date' => $room->last_maintenance_date?->format('Y-m-d H:i:s'),
            'next_maintenance_date' => $room->next_maintenance_date?->format('Y-m-d H:i:s'),
            'full_location' => $room->full_location ?? $room->location,
            'equipment_list' => $room->equipment_list ?? '',
            'is_available' => $room->is_available ?? true,
            'status_color' => $room->getStatusColor() ?? 'green',
            'type_icon' => $room->getTypeIcon() ?? 'building',
            'nextAppointment' => $nextAppointment ? [
                'id' => $nextAppointment->id,
                'start_at' => $nextAppointment->start_at->format('Y-m-d H:i:s'),
                'end_at' => $nextAppointment->end_at->format('Y-m-d H:i:s'),
                'patient_name' => $nextAppointment->patient?->name ?? 'Unknown',
                'doctor_name' => $nextAppointment->doctor?->name ?? 'Unknown',
                'type' => $nextAppointment->appointment_type ?? 'consultation',
                'status' => $nextAppointment->status,
            ] : null,
            'currentAppointment' => $currentAppointment ? [
                'id' => $currentAppointment->id,
                'start_at' => $currentAppointment->start_at->format('Y-m-d H:i:s'),
                'end_at' => $currentAppointment->end_at->format('Y-m-d H:i:s'),
                'patient_name' => $currentAppointment->patient?->name ?? 'Unknown',
                'doctor_name' => $currentAppointment->doctor?->name ?? 'Unknown',
                'type' => $currentAppointment->appointment_type ?? 'consultation',
                'status' => $currentAppointment->status,
            ] : null,
            'created_at' => $room->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get room statistics
     */
    public function statistics(Request $request)
    {
        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            
            $stats = [
                'total_rooms' => Room::where('clinic_id', $clinicId)->count(),
                'available_rooms' => Room::where('clinic_id', $clinicId)->available()->count(),
                'occupied_rooms' => Room::where('clinic_id', $clinicId)->byStatus(Room::STATUS_OCCUPIED)->count(),
                'maintenance_rooms' => Room::where('clinic_id', $clinicId)->byStatus(Room::STATUS_MAINTENANCE)->count(),
                'rooms_by_type' => Room::where('clinic_id', $clinicId)
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'rooms_needing_maintenance' => Room::where('clinic_id', $clinicId)
                    ->where('next_maintenance_date', '<=', now()->addDays(7))
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::statistics');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve room statistics'
            ], 500);
        }
    }

    /**
     * Update room status
     */
    public function updateStatus(Request $request, $id)
    {
        $rules = [
            'status' => 'required|string|in:Available,Occupied,Maintenance,Out of Service,Cleaning',
            'notes' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $room = Room::findOrFail($id);
            
            $oldStatus = $room->status;
            $room->update([
                'status' => $request->status,
                'maintenance_notes' => $request->notes ? 
                    ($room->maintenance_notes ? $room->maintenance_notes . "\n" . $request->notes : $request->notes) : 
                    $room->maintenance_notes
            ]);

            // Log the status change
            $this->logWebRequest('Room Status Update', [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room status updated successfully',
                'room' => $this->getRoom($room->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::updateStatus');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room status'
            ], 500);
        }
    }

    /**
     * Get available rooms for booking
     */
    public function available(Request $request)
    {
        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $type = $request->get('type');
            $date = $request->get('date');
            $time = $request->get('time');
            $duration = $request->get('duration', 30);

            $query = Room::where('clinic_id', $clinicId)
                ->active()
                ->where('status', Room::STATUS_AVAILABLE);

            if ($type) {
                $query->byType($type);
            }

            $rooms = $query->get()->map(function ($room) use ($date, $time, $duration) {
                $isAvailable = true;
                $nextAvailableSlot = null;
                
                if ($date && $time) {
                    $startTime = \Carbon\Carbon::parse($date . ' ' . $time);
                    $endTime = $startTime->copy()->addMinutes($duration);
                    $isAvailable = $room->isAvailableForTimeSlot($startTime, $endTime);
                } else {
                    $nextAvailableSlot = $room->getNextAvailableSlot($duration);
                }

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'type' => $room->type,
                    'capacity' => $room->capacity,
                    'location' => $room->full_location,
                    'equipment' => $room->equipment ?? [],
                    'is_available' => $isAvailable,
                    'next_available_slot' => $nextAvailableSlot,
                    'can_be_booked' => $room->canBeBooked(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $rooms
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::available');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available rooms'
            ], 500);
        }
    }

    /**
     * Get room availability for a specific date
     */
    public function availability(Request $request, $id)
    {
        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $room = Room::findOrFail($id);
            $date = $request->get('date', now()->format('Y-m-d'));

            $availability = $room->getAvailabilityForDate($date);

            return response()->json([
                'success' => true,
                'data' => [
                    'room' => [
                        'id' => $room->id,
                        'name' => $room->name,
                        'type' => $room->type,
                        'capacity' => $room->capacity,
                        'status' => $room->status,
                        'is_available' => $room->is_available,
                    ],
                    'date' => $date,
                    'availability' => $availability,
                    'next_available_slot' => $room->getNextAvailableSlot(),
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::availability');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve room availability'
            ], 500);
        }
    }

    /**
     * Check room availability for a specific time slot
     */
    public function checkAvailability(Request $request, $id)
    {
        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $rules = [
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'exclude_appointment_id' => 'nullable|integer',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room = Room::findOrFail($id);
            $startTime = \Carbon\Carbon::parse($request->start_time);
            $endTime = \Carbon\Carbon::parse($request->end_time);
            $excludeAppointmentId = $request->exclude_appointment_id;

            $isAvailable = $room->isAvailableForTimeSlot($startTime, $endTime, $excludeAppointmentId);

            return response()->json([
                'success' => true,
                'data' => [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'is_available' => $isAvailable,
                    'can_be_booked' => $room->canBeBooked(),
                    'status' => $room->status,
                ]
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::checkAvailability');
            return response()->json([
                'success' => false,
                'message' => 'Failed to check room availability'
            ], 500);
        }
    }

    /**
     * Bulk update room status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $rules = [
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'integer|exists:rooms,id',
            'status' => 'required|string|in:Available,Occupied,Maintenance,Out of Service,Cleaning',
            'notes' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $roomIds = $request->room_ids;
            $status = $request->status;
            $notes = $request->notes;

            // Verify all rooms belong to the user's clinic
            $rooms = Room::where('clinic_id', $clinicId)
                ->whereIn('id', $roomIds)
                ->get();

            if ($rooms->count() !== count($roomIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some rooms do not belong to your clinic'
                ], 403);
            }

            $updatedCount = 0;
            foreach ($rooms as $room) {
                $oldStatus = $room->status;
                $room->update([
                    'status' => $status,
                    'maintenance_notes' => $notes ? 
                        ($room->maintenance_notes ? $room->maintenance_notes . "\n" . $notes : $notes) : 
                        $room->maintenance_notes
                ]);
                $updatedCount++;

                // Log the status change
                $this->logWebRequest('Room Status Update (Bulk)', [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'notes' => $notes
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} room(s) status",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::bulkUpdateStatus');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room statuses'
            ], 500);
        }
    }

    /**
     * Bulk delete rooms
     */
    public function bulkDelete(Request $request)
    {
        $rules = [
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'integer|exists:rooms,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $roomIds = $request->room_ids;

            // Verify all rooms belong to the user's clinic
            $rooms = Room::where('clinic_id', $clinicId)
                ->whereIn('id', $roomIds)
                ->get();

            if ($rooms->count() !== count($roomIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some rooms do not belong to your clinic'
                ], 403);
            }

            $deletedCount = 0;
            foreach ($rooms as $room) {
                // Check if room has appointments
                $hasAppointments = $room->appointments()->count() > 0;
                
                if ($hasAppointments) {
                    return response()->json([
                        'success' => false,
                        'message' => "Room '{$room->name}' cannot be deleted as it has associated appointments"
                    ], 422);
                }

                $room->delete();
                $deletedCount++;

                // Log the deletion
                $this->logWebRequest('Room Deleted (Bulk)', [
                    'room_id' => $room->id,
                    'room_name' => $room->name
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} room(s)",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::bulkDelete');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rooms'
            ], 500);
        }
    }

    /**
     * Export rooms data
     */
    public function export(Request $request)
    {
        try {
            $userClinicRole = $this->getUserClinicRole($request);
            
            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $format = $request->get('format', 'csv');

            $rooms = Room::where('clinic_id', $clinicId)
                ->with(['upcomingAppointments.patient', 'upcomingAppointments.doctor'])
                ->get()
                ->map(function ($room) {
                    return [
                        'ID' => $room->id,
                        'Name' => $room->name,
                        'Type' => $room->type,
                        'Capacity' => $room->capacity,
                        'Status' => $room->status,
                        'Location' => $room->location,
                        'Floor' => $room->floor_number,
                        'Wing' => $room->wing,
                        'Description' => $room->description,
                        'Equipment' => is_array($room->equipment) ? implode(', ', $room->equipment) : '',
                        'Maintenance Notes' => $room->maintenance_notes,
                        'Special Requirements' => $room->special_requirements,
                        'Is Active' => $room->is_active ? 'Yes' : 'No',
                        'Next Appointment' => $room->nextAppointment ? 
                            $room->nextAppointment->start_at->format('Y-m-d H:i:s') : 'None',
                        'Next Appointment Patient' => $room->nextAppointment?->patient?->name ?? 'N/A',
                        'Next Appointment Doctor' => $room->nextAppointment?->doctor?->name ?? 'N/A',
                        'Created At' => $room->created_at->format('Y-m-d H:i:s'),
                        'Updated At' => $room->updated_at->format('Y-m-d H:i:s'),
                    ];
                });

            if ($format === 'csv') {
                $filename = 'rooms_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($rooms) {
                    $file = fopen('php://output', 'w');
                    
                    // Write headers
                    if ($rooms->isNotEmpty()) {
                        fputcsv($file, array_keys($rooms->first()));
                    }
                    
                    // Write data
                    foreach ($rooms as $room) {
                        fputcsv($file, $room);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            return response()->json([
                'success' => true,
                'data' => $rooms
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'RoomController::export');
            return response()->json([
                'success' => false,
                'message' => 'Failed to export rooms data'
            ], 500);
        }
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
