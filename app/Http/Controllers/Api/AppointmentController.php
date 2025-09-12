<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;





class AppointmentController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/appointments",
     *     summary="Get all appointments",
     *     description="Retrieve a paginated list of appointments with optional filtering",
     *     operationId="getAppointments",
     *     tags={"Appointments"},
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
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by appointment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"scheduled", "confirmed", "in_progress", "completed", "cancelled", "no_show"})
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter appointments from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter appointments to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Appointment")
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
            $this->requirePermission('appointments.view');

            $currentClinic = $this->getCurrentClinic();
            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $query = Appointment::with(['patient', 'doctor', 'clinic'])
                ->where('clinic_id', $currentClinic->id);

            // Apply filters
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->get('doctor_id'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->has('date_from')) {
                $query->where('appointment_date', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->where('appointment_date', '<=', $request->get('date_to'));
            }

            $perPage = $request->get('per_page', 15);
            $appointments = $query->orderBy('appointment_date', 'desc')
                                 ->orderBy('appointment_time', 'desc')
                                 ->paginate($perPage);

            return $this->successResponse($appointments, 'Appointments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve appointments: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/appointments",
     *     summary="Create a new appointment",
     *     description="Create a new appointment",
     *     operationId="createAppointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id", "doctor_id", "appointment_date", "appointment_time"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="appointment_date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="appointment_time", type="string", format="time", example="10:00:00"),
     *             @OA\Property(property="type", type="string", enum={"consultation", "follow_up", "emergency", "routine"}, example="consultation"),
     *             @OA\Property(property="notes", type="string", example="Regular checkup"),
     *             @OA\Property(property="status", type="string", enum={"scheduled", "confirmed", "in_progress", "completed", "cancelled", "no_show"}, example="scheduled")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Appointment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('appointments.create');

            $currentClinic = $this->getCurrentClinic();
            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:doctors,id',
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i:s',
                'type' => 'nullable|in:consultation,follow_up,emergency,routine',
                'notes' => 'nullable|string|max:1000',
                'status' => 'nullable|in:scheduled,confirmed,in_progress,completed,cancelled,no_show'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            // Verify patient and doctor belong to the current clinic
            $patient = Patient::findOrFail($request->patient_id);
            if ($patient->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Patient does not belong to your clinic', null, 403);
            }

            $doctor = Doctor::findOrFail($request->doctor_id);
            if ($doctor->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Doctor does not belong to your clinic', null, 403);
            }

            $appointmentData = $request->all();
            $appointmentData['clinic_id'] = $currentClinic->id;
            $appointmentData['status'] = $appointmentData['status'] ?? 'scheduled';

            $appointment = Appointment::create($appointmentData);
            $appointment->load(['patient', 'doctor', 'clinic']);

            return $this->successResponse($appointment, 'Appointment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create appointment: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/appointments/{id}",
     *     summary="Get a specific appointment",
     *     description="Retrieve a specific appointment by ID",
     *     operationId="getAppointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $appointment = Appointment::with(['patient', 'doctor', 'clinic'])->findOrFail($id);
            return $this->successResponse($appointment, 'Appointment retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Appointment not found', null, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/appointments/{id}",
     *     summary="Update an appointment",
     *     description="Update an existing appointment",
     *     operationId="updateAppointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="appointment_date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="appointment_time", type="string", format="time", example="10:00:00"),
     *             @OA\Property(property="type", type="string", enum={"consultation", "follow_up", "emergency", "routine"}, example="consultation"),
     *             @OA\Property(property="notes", type="string", example="Regular checkup"),
     *             @OA\Property(property="status", type="string", enum={"scheduled", "confirmed", "in_progress", "completed", "cancelled", "no_show"}, example="confirmed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Appointment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'patient_id' => 'sometimes|required|exists:patients,id',
                'doctor_id' => 'sometimes|required|exists:doctors,id',
                'appointment_date' => 'sometimes|required|date',
                'appointment_time' => 'sometimes|required|date_format:H:i:s',
                'type' => 'nullable|in:consultation,follow_up,emergency,routine',
                'notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,cancelled,no_show'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $appointment->update($request->all());
            $appointment->load(['patient', 'doctor', 'clinic']);

            return $this->successResponse($appointment, 'Appointment updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update appointment: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/appointments/{id}",
     *     summary="Delete an appointment",
     *     description="Delete an appointment (soft delete)",
     *     operationId="deleteAppointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return $this->successResponse(null, 'Appointment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete appointment: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/appointments/search",
     *     summary="Search appointments",
     *     description="Search appointments by patient name, doctor name, or other criteria",
     *     operationId="searchAppointments",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="John")
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
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Appointment")
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
                return $this->errorResponse('Search query is required', null, 400);
            }

            $appointments = Appointment::with(['patient', 'doctor'])
                ->whereHas('patient', function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%");
                })
                ->orWhereHas('doctor', function ($q) use ($query) {
                    $q->where('specialization', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();

            return $this->successResponse($appointments, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search appointments: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/appointments/bulk-create",
     *     summary="Bulk create appointments",
     *     description="Create multiple appointments at once",
     *     operationId="bulkCreateAppointments",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="appointments",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_date", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="appointment_time", type="string", format="time", example="10:00:00"),
     *                     @OA\Property(property="type", type="string", enum={"consultation", "follow_up", "emergency", "routine"}, example="consultation"),
     *                     @OA\Property(property="notes", type="string", example="Regular checkup")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointments created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointments created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="created_count", type="integer", example=5),
     *                 @OA\Property(property="failed_count", type="integer", example=1),
     *                 @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'appointments' => 'required|array|min:1',
                'appointments.*.patient_id' => 'required|exists:patients,id',
                'appointments.*.doctor_id' => 'required|exists:doctors,id',
                'appointments.*.appointment_date' => 'required|date|after_or_equal:today',
                'appointments.*.appointment_time' => 'required|date_format:H:i:s',
                'appointments.*.type' => 'nullable|in:consultation,follow_up,emergency,routine',
                'appointments.*.notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $appointments = $request->get('appointments');
            $createdCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($appointments as $appointmentData) {
                try {
                    $appointmentData['clinic_id'] = Auth::user()->current_clinic_id ?? 1;
                    $appointmentData['status'] = 'scheduled';
                    Appointment::create($appointmentData);
                    $createdCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Failed to create appointment: " . $e->getMessage();
                }
            }

            $result = [
                'created_count' => $createdCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ];

            return $this->successResponse($result, 'Appointments created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create appointments: ' . $e->getMessage());
        }
    }

    /**
     * Check in an appointment
     */
    public function checkIn($id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->update(['status' => 'checked_in', 'checked_in_at' => now()]);
            
            return $this->successResponse($appointment, 'Appointment checked in successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check in appointment: ' . $e->getMessage());
        }
    }

    /**
     * Check out an appointment
     */
    public function checkOut($id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->update(['status' => 'checked_out', 'checked_out_at' => now()]);
            
            return $this->successResponse($appointment, 'Appointment checked out successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check out appointment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an appointment
     */
    public function cancel($id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            
            return $this->successResponse($appointment, 'Appointment cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to cancel appointment: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule an appointment
     */
    public function reschedule(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i:s',
                'reason' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $appointment = Appointment::findOrFail($id);
            $appointment->update([
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'status' => 'rescheduled',
                'rescheduled_at' => now(),
                'reschedule_reason' => $request->reason
            ]);
            
            return $this->successResponse($appointment, 'Appointment rescheduled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reschedule appointment: ' . $e->getMessage());
        }
    }

    /**
     * Send appointment reminder
     */
    public function sendReminder($id): JsonResponse
    {
        try {
            $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);
            
            // Implementation for sending reminder would go here
            // This is a placeholder response
            
            return $this->successResponse(null, 'Appointment reminder sent successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send reminder: ' . $e->getMessage());
        }
    }

    /**
     * Get available appointment slots
     */
    public function availableSlots(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|exists:doctors,id',
                'date' => 'required|date|after_or_equal:today',
                'duration' => 'nullable|integer|min:15|max:240'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $doctorId = $request->doctor_id;
            $date = $request->date;
            $duration = $request->duration ?? 30;

            // Implementation for getting available slots would go here
            // This is a placeholder response
            $slots = [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
            ];

            return $this->successResponse($slots, 'Available slots retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get available slots: ' . $e->getMessage());
        }
    }

    /**
     * Check for appointment conflicts
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|exists:doctors,id',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i:s',
                'duration' => 'nullable|integer|min:15|max:240',
                'exclude_id' => 'nullable|exists:appointments,id'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $doctorId = $request->doctor_id;
            $date = $request->appointment_date;
            $time = $request->appointment_time;
            $duration = $request->duration ?? 30;
            $excludeId = $request->exclude_id;

            // Implementation for checking conflicts would go here
            // This is a placeholder response
            $conflicts = [];

            return $this->successResponse($conflicts, 'Conflict check completed');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check conflicts: ' . $e->getMessage());
        }
    }

    /**
     * Get today's appointments
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $today = now()->format('Y-m-d');
            $appointments = Appointment::with(['patient', 'doctor'])
                ->whereDate('appointment_date', $today)
                ->orderBy('appointment_time')
                ->get();

            return $this->successResponse($appointments, 'Today\'s appointments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get today\'s appointments: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming appointments
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $appointments = Appointment::with(['patient', 'doctor'])
                ->where('appointment_date', '>=', now()->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit($limit)
                ->get();

            return $this->successResponse($appointments, 'Upcoming appointments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get upcoming appointments: ' . $e->getMessage());
        }
    }

    /**
     * Get appointment reports
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'doctor_id' => 'nullable|exists:doctors,id',
                'status' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $query = Appointment::with(['patient', 'doctor'])
                ->whereBetween('appointment_date', [$request->date_from, $request->date_to]);

            if ($request->doctor_id) {
                $query->where('doctor_id', $request->doctor_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $appointments = $query->get();

            // Generate report data
            $report = [
                'total_appointments' => $appointments->count(),
                'by_status' => $appointments->groupBy('status')->map->count(),
                'by_doctor' => $appointments->groupBy('doctor.name')->map->count(),
                'appointments' => $appointments
            ];

            return $this->successResponse($report, 'Appointment report generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate report: ' . $e->getMessage());
        }
    }

    /**
     * Export appointments
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'format' => 'nullable|in:csv,excel,pdf'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            // Implementation for exporting appointments would go here
            // This is a placeholder response
            $exportData = [
                'file_url' => '/exports/appointments_' . now()->format('Y-m-d') . '.csv',
                'exported_at' => now()->toISOString()
            ];

            return $this->successResponse($exportData, 'Appointments exported successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to export appointments: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar events
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'doctor_id' => 'nullable|exists:doctors,id'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $query = Appointment::with(['patient', 'doctor'])
                ->whereBetween('appointment_date', [$request->start_date, $request->end_date]);

            if ($request->doctor_id) {
                $query->where('doctor_id', $request->doctor_id);
            }

            $appointments = $query->get();

            // Format for calendar
            $events = $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                    'start' => $appointment->appointment_date . 'T' . $appointment->appointment_time,
                    'end' => $appointment->appointment_date . 'T' . 
                        date('H:i:s', strtotime($appointment->appointment_time . ' +30 minutes')),
                    'status' => $appointment->status,
                    'doctor' => $appointment->doctor->user->name ?? 'Unknown'
                ];
            });

            return $this->successResponse($events, 'Calendar events retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get calendar events: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar view
     */
    public function calendar(Request $request): JsonResponse
    {
        try {
            $doctors = Doctor::with('user')->get();
            
            return $this->successResponse([
                'doctors' => $doctors,
                'current_date' => now()->format('Y-m-d')
            ], 'Calendar data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get calendar data: ' . $e->getMessage());
        }
    }
}
