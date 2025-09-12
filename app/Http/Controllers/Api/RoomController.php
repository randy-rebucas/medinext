<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Room;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Room",
 *     type="object",
 *     title="Room",
 *     description="Room model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Room 101"),
 *     @OA\Property(property="room_number", type="string", example="101"),
 *     @OA\Property(property="room_type", type="string", enum={"consultation", "examination", "procedure", "waiting", "office"}, example="consultation"),
 *     @OA\Property(property="capacity", type="integer", example=4),
 *     @OA\Property(property="equipment", type="array", @OA\Items(type="string"), example={"Examination table", "Computer", "Blood pressure monitor"}),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */



class RoomController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/rooms",
     *     summary="Get all rooms",
     *     description="Retrieve a paginated list of all rooms in the system",
     *     tags={"Room Management"},
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
     *         name="clinic_id",
     *         in="query",
     *         description="Filter by clinic ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="room_type",
     *         in="query",
     *         description="Filter by room type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"consultation", "examination", "procedure", "waiting", "office"}, example="consultation")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rooms retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rooms retrieved successfully"),
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
            $query = Room::with('clinic');

            // Apply filters
            if ($request->has('clinic_id')) {
                $query->where('clinic_id', $request->get('clinic_id'));
            }

            if ($request->has('room_type')) {
                $query->where('room_type', $request->get('room_type'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->get('is_active'));
            }

            $rooms = $query->orderBy('name')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($rooms, 'Rooms retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/rooms",
     *     summary="Create a new room",
     *     description="Create a new room in a clinic",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"clinic_id", "name", "room_number", "room_type"},
     *             @OA\Property(property="clinic_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Room 101"),
     *             @OA\Property(property="room_number", type="string", example="101"),
     *             @OA\Property(property="room_type", type="string", enum={"consultation", "examination", "procedure", "waiting", "office"}, example="consultation"),
     *             @OA\Property(property="capacity", type="integer", example=4),
     *             @OA\Property(
     *                 property="equipment",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Examination table", "Computer", "Blood pressure monitor"}
     *             ),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="room", type="object")
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
                'clinic_id' => 'required|integer|exists:clinics,id',
                'name' => 'required|string|max:255',
                'room_number' => 'required|string|max:50',
                'room_type' => 'required|string|in:consultation,examination,procedure,waiting,office',
                'capacity' => 'nullable|integer|min:1',
                'equipment' => 'nullable|array',
                'equipment.*' => 'string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $room = Room::create([
                'clinic_id' => $request->clinic_id,
                'name' => $request->name,
                'room_number' => $request->room_number,
                'room_type' => $request->room_type,
                'capacity' => $request->get('capacity', 1),
                'equipment' => $request->get('equipment', []),
                'is_active' => $request->get('is_active', true),
            ]);

            $room->load('clinic');

            return $this->successResponse(['room' => $room], 'Room created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/rooms/{id}",
     *     summary="Get room by ID",
     *     description="Retrieve a specific room by its ID",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="room", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $room = Room::with('clinic')->findOrFail($id);

            return $this->successResponse(['room' => $room], 'Room retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/rooms/{id}",
     *     summary="Update room",
     *     description="Update an existing room's information",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Room 101"),
     *             @OA\Property(property="room_number", type="string", example="101"),
     *             @OA\Property(property="room_type", type="string", enum={"consultation", "examination", "procedure", "waiting", "office"}, example="consultation"),
     *             @OA\Property(property="capacity", type="integer", example=6),
     *             @OA\Property(
     *                 property="equipment",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Examination table", "Computer", "Blood pressure monitor", "Stethoscope"}
     *             ),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="room", type="object")
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
     *         description="Room not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'room_number' => 'sometimes|required|string|max:50',
                'room_type' => 'sometimes|required|string|in:consultation,examination,procedure,waiting,office',
                'capacity' => 'nullable|integer|min:1',
                'equipment' => 'nullable|array',
                'equipment.*' => 'string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $room->update($request->only(['name', 'room_number', 'room_type', 'capacity', 'equipment', 'is_active']));
            $room->load('clinic');

            return $this->successResponse(['room' => $room], 'Room updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/rooms/{id}",
     *     summary="Delete room",
     *     description="Delete a room from the system",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found",
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
            $room = Room::findOrFail($id);
            $room->delete();

            return $this->successResponse(null, 'Room deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/rooms/{id}/availability",
     *     summary="Get room availability",
     *     description="Check room availability for a specific date and time",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date to check availability (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-15")
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="Start time (HH:MM)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="10:00")
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="End time (HH:MM)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="11:00")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room availability retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room availability retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="room_id", type="integer", example=1),
     *                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="is_available", type="boolean", example=true),
     *                 @OA\Property(property="available_slots", type="array", @OA\Items(type="string"), example={"09:00-10:00", "11:00-12:00"}),
     *                 @OA\Property(property="booked_slots", type="array", @OA\Items(type="string"), example={"10:00-11:00"})
     *             )
     *         )
     *     )
     * )
     */
    public function availability($id, Request $request): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $date = $request->get('date', now()->format('Y-m-d'));
            $startTime = $request->get('start_time');
            $endTime = $request->get('end_time');

            // TODO: Implement actual availability checking logic
            // This would typically check against appointments or bookings

            $isAvailable = true; // Placeholder logic
            $availableSlots = ['09:00-10:00', '11:00-12:00', '14:00-15:00'];
            $bookedSlots = ['10:00-11:00', '13:00-14:00'];

            if ($startTime && $endTime) {
                // Check specific time slot
                $isAvailable = !in_array("{$startTime}-{$endTime}", $bookedSlots);
            }

            return $this->successResponse([
                'room_id' => $room->id,
                'date' => $date,
                'is_available' => $isAvailable,
                'available_slots' => $availableSlots,
                'booked_slots' => $bookedSlots
            ], 'Room availability retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/rooms/{id}/book",
     *     summary="Book room",
     *     description="Book a room for a specific time slot",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date", "start_time", "end_time", "purpose"},
     *             @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="start_time", type="string", format="time", example="10:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="11:00"),
     *             @OA\Property(property="purpose", type="string", example="Patient consultation"),
     *             @OA\Property(property="appointment_id", type="integer", example=1, description="Optional appointment ID if booking is for an appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room booked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room booked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="booking_id", type="integer", example=1),
     *                 @OA\Property(property="room_id", type="integer", example=1),
     *                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="start_time", type="string", format="time", example="10:00"),
     *                 @OA\Property(property="end_time", type="string", format="time", example="11:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or room not available",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function book($id, Request $request): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'purpose' => 'required|string|max:255',
                'appointment_id' => 'nullable|integer|exists:appointments,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // TODO: Implement actual booking logic
            // This would typically create a room booking record

            $bookingId = 1; // Placeholder

            return $this->successResponse([
                'booking_id' => $bookingId,
                'room_id' => $room->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ], 'Room booked successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/rooms/{id}/release",
     *     summary="Release room booking",
     *     description="Release a room booking",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id"},
     *             @OA\Property(property="booking_id", type="integer", example=1),
     *             @OA\Property(property="reason", type="string", example="Appointment cancelled")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room booking released successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Room booking released successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function release($id, Request $request): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'booking_id' => 'required|integer',
                'reason' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // TODO: Implement actual release logic
            // This would typically update or delete the room booking record

            return $this->successResponse(null, 'Room booking released successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/rooms/available",
     *     summary="Get available rooms",
     *     description="Get list of available rooms for a specific date and time",
     *     tags={"Room Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date to check availability (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-15")
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         description="Start time (HH:MM)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="10:00")
     *     ),
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="End time (HH:MM)",
     *         required=false,
     *         @OA\Schema(type="string", format="time", example="11:00")
     *     ),
     *     @OA\Parameter(
     *         name="room_type",
     *         in="query",
     *         description="Filter by room type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"consultation", "examination", "procedure", "waiting", "office"}, example="consultation")
     *     ),
     *     @OA\Parameter(
     *         name="clinic_id",
     *         in="query",
     *         description="Filter by clinic ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available rooms retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Available rooms retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="rooms",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="start_time", type="string", format="time", example="10:00"),
     *                 @OA\Property(property="end_time", type="string", format="time", example="11:00")
     *             )
     *         )
     *     )
     * )
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', now()->format('Y-m-d'));
            $startTime = $request->get('start_time');
            $endTime = $request->get('end_time');
            $roomType = $request->get('room_type');
            $clinicId = $request->get('clinic_id');

            $query = Room::with('clinic')->where('is_active', true);

            if ($clinicId) {
                $query->where('clinic_id', $clinicId);
            }

            if ($roomType) {
                $query->where('room_type', $roomType);
            }

            $rooms = $query->get();

            // TODO: Filter rooms based on actual availability
            // This would typically check against existing bookings

            return $this->successResponse([
                'rooms' => $rooms,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ], 'Available rooms retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
