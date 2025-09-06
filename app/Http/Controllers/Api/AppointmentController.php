<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

class AppointmentController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/appointments",
     *     summary="Get all appointments",
     *     description="Retrieve a paginated list of appointments for the current clinic",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"scheduled","confirmed","in_progress","completed","cancelled","no_show","rescheduled","waiting","checked_in","checked_out"})
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="appointment_type",
     *         in="query",
     *         description="Filter by appointment type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","routine_checkup","specialist_consultation","procedure","surgery","lab_test","imaging","physical_therapy"})
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","start_at","end_at","status","created_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Appointment")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'start_at', 'end_at', 'status', 'created_at'
            ]);

            $query = Appointment::where('clinic_id', $currentClinic->id)
                ->with(['patient', 'doctor.user', 'clinic', 'room']);

            // Filter by date range
            if ($request->has('date_from') || $request->has('date_to')) {
                if ($request->has('date_from')) {
                    $query->where('start_at', '>=', $request->get('date_from'));
                }
                if ($request->has('date_to')) {
                    $query->where('start_at', '<=', $request->get('date_to'));
                }
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by doctor
            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->get('doctor_id'));
            }

            // Filter by patient
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            // Filter by appointment type
            if ($request->has('appointment_type')) {
                $query->where('appointment_type', $request->get('appointment_type'));
            }

            $appointments = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($appointments, 'Appointments retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/appointments",
     *     summary="Create a new appointment",
     *     description="Create a new appointment with conflict checking",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id","doctor_id","start_at","duration","appointment_type"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="start_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *             @OA\Property(property="duration", type="integer", example=30, description="Duration in minutes"),
     *             @OA\Property(property="appointment_type", type="string", enum={"consultation","follow_up","emergency","routine_checkup","specialist_consultation","procedure","surgery","lab_test","imaging","physical_therapy"}, example="consultation"),
     *             @OA\Property(property="reason", type="string", example="Regular checkup"),
     *             @OA\Property(property="room_id", type="integer", example=1),
     *             @OA\Property(property="priority", type="string", enum={"low","normal","high","urgent","emergency"}, example="normal"),
     *             @OA\Property(property="notes", type="string", example="Patient prefers morning appointments"),
     *             @OA\Property(
     *                 property="insurance_info",
     *                 type="object",
     *                 @OA\Property(property="provider", type="string", example="Blue Cross"),
     *                 @OA\Property(property="policy_number", type="string", example="BC123456")
     *             ),
     *             @OA\Property(property="copay_amount", type="number", format="float", example=25.00),
     *             @OA\Property(property="total_amount", type="number", format="float", example=150.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Appointment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="appointment", ref="#/components/schemas/Appointment")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or time slot not available",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|integer|exists:patients,id',
                'doctor_id' => 'required|integer|exists:doctors,id',
                'start_at' => 'required|date|after:now',
                'duration' => 'required|integer|min:15|max:480', // 15 minutes to 8 hours
                'appointment_type' => 'required|string|in:consultation,follow_up,emergency,routine_checkup,specialist_consultation,procedure,surgery,lab_test,imaging,physical_therapy',
                'reason' => 'nullable|string|max:500',
                'room_id' => 'nullable|integer|exists:rooms,id',
                'priority' => 'nullable|string|in:low,normal,high,urgent,emergency',
                'notes' => 'nullable|string|max:1000',
                'insurance_info' => 'nullable|array',
                'copay_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $currentClinic->id;
            $data['status'] = 'scheduled';
            $data['source'] = 'api';

            // Check if patient belongs to the same clinic
            $patient = Patient::findOrFail($data['patient_id']);
            if ($patient->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Patient does not belong to this clinic', null, 422);
            }

            // Check if doctor belongs to the same clinic
            $doctor = Doctor::findOrFail($data['doctor_id']);
            if ($doctor->clinic_id !== $currentClinic->id) {
                return $this->errorResponse('Doctor does not belong to this clinic', null, 422);
            }

            // Check for conflicts
            $startTime = Carbon::parse($data['start_at']);
            $endTime = $startTime->copy()->addMinutes($data['duration']);

            if (!Appointment::isTimeSlotAvailable($data['doctor_id'], $startTime, $data['duration'], $data['room_id'] ?? null)) {
                return $this->errorResponse('Time slot is not available', null, 422);
            }

            $appointment = Appointment::create($data);
            $appointment->load(['patient', 'doctor.user', 'clinic', 'room']);

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Appointment created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/appointments/{appointment}",
     *     summary="Get appointment details",
     *     description="Retrieve detailed information about a specific appointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="appointment", ref="#/components/schemas/Appointment"),
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="duration_minutes", type="integer", example=30),
     *                     @OA\Property(property="status_history", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(
     *                     property="timeline",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            $appointment->load(['patient', 'doctor.user', 'clinic', 'room', 'encounter']);

            return $this->successResponse([
                'appointment' => $appointment,
                'statistics' => $appointment->statistics,
                'timeline' => $appointment->timeline,
            ], 'Appointment retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified appointment
     */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            $validator = Validator::make($request->all(), [
                'patient_id' => 'sometimes|required|integer|exists:patients,id',
                'doctor_id' => 'sometimes|required|integer|exists:doctors,id',
                'start_at' => 'sometimes|required|date|after:now',
                'duration' => 'sometimes|required|integer|min:15|max:480',
                'appointment_type' => 'sometimes|required|string|in:consultation,follow_up,emergency,routine_checkup,specialist_consultation,procedure,surgery,lab_test,imaging,physical_therapy',
                'reason' => 'nullable|string|max:500',
                'room_id' => 'nullable|integer|exists:rooms,id',
                'priority' => 'nullable|string|in:low,normal,high,urgent,emergency',
                'notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|required|string|in:scheduled,confirmed,in_progress,completed,cancelled,no_show,rescheduled,waiting,checked_in,checked_out',
                'insurance_info' => 'nullable|array',
                'copay_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();

            // Check for conflicts if time is being changed
            if (isset($data['start_at']) || isset($data['duration']) || isset($data['doctor_id'])) {
                $startTime = Carbon::parse($data['start_at'] ?? $appointment->start_at);
                $duration = $data['duration'] ?? $appointment->duration;
                $doctorId = $data['doctor_id'] ?? $appointment->doctor_id;
                $roomId = $data['room_id'] ?? $appointment->room_id;

                if (!Appointment::isTimeSlotAvailable($doctorId, $startTime, $duration, $roomId, $appointment->id)) {
                    return $this->errorResponse('Time slot is not available', null, 422);
                }
            }

            $appointment->update($data);
            $appointment->load(['patient', 'doctor.user', 'clinic', 'room']);

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Appointment updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified appointment
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            // Check if appointment can be deleted
            if (in_array($appointment->status, ['completed', 'in_progress'])) {
                return $this->errorResponse('Cannot delete completed or in-progress appointments', null, 422);
            }

            $appointment->delete();

            return $this->successResponse(null, 'Appointment deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/appointments/{appointment}/check-in",
     *     summary="Check in patient",
     *     description="Check in a patient for their appointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient checked in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient checked in successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="appointment", ref="#/components/schemas/Appointment"),
     *                 @OA\Property(property="checked_in_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot check in this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function checkIn(Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            if ($appointment->status !== 'scheduled' && $appointment->status !== 'confirmed') {
                return $this->errorResponse('Appointment is not in a check-in state', null, 422);
            }

            $appointment->checkIn();
            $appointment->load(['patient', 'doctor.user', 'clinic', 'room']);

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Patient checked in successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/appointments/{appointment}/check-out",
     *     summary="Check out appointment",
     *     description="Check out a patient from their appointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="checkout_notes", type="string", example="Patient completed treatment successfully", description="Notes about the checkout"),
     *             @OA\Property(property="next_appointment_required", type="boolean", example=true, description="Whether a follow-up appointment is needed"),
     *             @OA\Property(property="follow_up_date", type="string", format="date", example="2024-01-22", description="Suggested follow-up date"),
     *             @OA\Property(property="prescription_given", type="boolean", example=true, description="Whether prescriptions were provided"),
     *             @OA\Property(property="lab_orders_placed", type="boolean", example=false, description="Whether lab orders were placed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment checked out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment checked out successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="appointment", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="check_in_time", type="string", format="date-time", example="2024-01-15T10:05:00Z"),
     *                     @OA\Property(property="check_out_time", type="string", format="date-time", example="2024-01-15T10:45:00Z"),
     *                     @OA\Property(property="duration_minutes", type="integer", example=40),
     *                     @OA\Property(property="checkout_notes", type="string", example="Patient completed treatment successfully"),
     *                     @OA\Property(property="next_appointment_required", type="boolean", example=true),
     *                     @OA\Property(property="follow_up_date", type="string", format="date", example="2024-01-22"),
     *                     @OA\Property(property="prescription_given", type="boolean", example=true),
     *                     @OA\Property(property="lab_orders_placed", type="boolean", example=false),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Appointment not checked in or already checked out",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function checkOut(Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            if (!in_array($appointment->status, ['checked_in', 'in_progress'])) {
                return $this->errorResponse('Patient must be checked in to check out', null, 422);
            }

            $appointment->checkOut();
            $appointment->load(['patient', 'doctor.user', 'clinic', 'room']);

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Patient checked out successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/appointments/{appointment}/cancel",
     *     summary="Cancel appointment",
     *     description="Cancel an existing appointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="cancellation_reason", type="string", example="Patient requested cancellation", description="Reason for cancellation"),
     *             @OA\Property(property="cancelled_by", type="string", enum={"patient","doctor","staff","system"}, example="patient", description="Who cancelled the appointment"),
     *             @OA\Property(property="reschedule_offered", type="boolean", example=true, description="Whether rescheduling was offered"),
     *             @OA\Property(property="refund_required", type="boolean", example=false, description="Whether a refund is required"),
     *             @OA\Property(property="notes", type="string", example="Patient will call back to reschedule", description="Additional cancellation notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment cancelled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="appointment", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="cancelled"),
     *                     @OA\Property(property="cancellation_reason", type="string", example="Patient requested cancellation"),
     *                     @OA\Property(property="cancelled_by", type="string", example="patient"),
     *                     @OA\Property(property="cancelled_at", type="string", format="date-time"),
     *                     @OA\Property(property="reschedule_offered", type="boolean", example=true),
     *                     @OA\Property(property="refund_required", type="boolean", example=false),
     *                     @OA\Property(property="notes", type="string", example="Patient will call back to reschedule"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Appointment already cancelled or validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            $validator = Validator::make($request->all(), [
                'cancellation_reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            if ($appointment->status === 'cancelled') {
                return $this->errorResponse('Appointment is already cancelled', null, 422);
            }

            $user = $this->getAuthenticatedUser();
            $appointment->cancel($request->cancellation_reason, $user->name);
            $appointment->load(['patient', 'doctor.user', 'clinic', 'room']);

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Appointment cancelled successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/appointments/{appointment}/reschedule",
     *     summary="Reschedule appointment",
     *     description="Reschedule an existing appointment to a new date and time",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="new_appointment_date", type="string", format="date-time", example="2024-01-22T14:00:00Z", description="New appointment date and time"),
     *             @OA\Property(property="reschedule_reason", type="string", example="Patient requested different time", description="Reason for rescheduling"),
     *             @OA\Property(property="rescheduled_by", type="string", enum={"patient","doctor","staff"}, example="patient", description="Who requested the reschedule"),
     *             @OA\Property(property="send_notification", type="boolean", example=true, description="Whether to send notification to patient"),
     *             @OA\Property(property="notes", type="string", example="Patient prefers afternoon appointments", description="Additional reschedule notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appointment rescheduled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Appointment rescheduled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="appointment", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-22T14:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="scheduled"),
     *                     @OA\Property(property="reschedule_reason", type="string", example="Patient requested different time"),
     *                     @OA\Property(property="rescheduled_by", type="string", example="patient"),
     *                     @OA\Property(property="rescheduled_at", type="string", format="date-time"),
     *                     @OA\Property(property="original_appointment_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="notes", type="string", example="Patient prefers afternoon appointments"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(
     *                     property="conflicts",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="type", type="string", example="doctor_unavailable"),
     *                         @OA\Property(property="message", type="string", example="Doctor has another appointment at this time"),
     *                         @OA\Property(property="severity", type="string", example="warning")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid new date or validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            $validator = Validator::make($request->all(), [
                'start_at' => 'required|date|after:now',
                'room_id' => 'nullable|integer|exists:rooms,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            if ($appointment->status === 'cancelled') {
                return $this->errorResponse('Cannot reschedule cancelled appointment', null, 422);
            }

            $newStartTime = Carbon::parse($request->start_at);
            $roomId = $request->get('room_id');

            // Check for conflicts
            if (!Appointment::isTimeSlotAvailable($appointment->doctor_id, $newStartTime, $appointment->duration, $roomId, $appointment->id)) {
                return $this->errorResponse('New time slot is not available', null, 422);
            }

            $appointment->reschedule($newStartTime, $roomId);
            $appointment->load(['patient', 'doctor.user', 'clinic', 'room']);

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Appointment rescheduled successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/appointments/{appointment}/reminder",
     *     summary="Send appointment reminder",
     *     description="Send a reminder notification for an upcoming appointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reminder_type", type="string", enum={"email","sms","push","all"}, example="all", description="Type of reminder to send"),
     *             @OA\Property(property="custom_message", type="string", example="Don't forget your appointment tomorrow at 10:00 AM", description="Custom reminder message"),
     *             @OA\Property(property="send_to_patient", type="boolean", example=true, description="Send reminder to patient"),
     *             @OA\Property(property="send_to_doctor", type="boolean", example=false, description="Send reminder to doctor"),
     *             @OA\Property(property="include_preparation_instructions", type="boolean", example=true, description="Include preparation instructions in reminder")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reminder sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reminder sent successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="reminder", type="object",
     *                     @OA\Property(property="appointment_id", type="integer", example=1),
     *                     @OA\Property(property="reminder_type", type="string", example="all"),
     *                     @OA\Property(property="sent_at", type="string", format="date-time", example="2024-01-14T18:00:00Z"),
     *                     @OA\Property(property="delivery_status", type="object",
     *                         @OA\Property(property="email", type="object",
     *                             @OA\Property(property="sent", type="boolean", example=true),
     *                             @OA\Property(property="delivered", type="boolean", example=true),
     *                             @OA\Property(property="message_id", type="string", example="msg_123456789")
     *                         ),
     *                         @OA\Property(property="sms", type="object",
     *                             @OA\Property(property="sent", type="boolean", example=true),
     *                             @OA\Property(property="delivered", type="boolean", example=true),
     *                             @OA\Property(property="message_id", type="string", example="sms_987654321")
     *                         ),
     *                         @OA\Property(property="push", type="object",
     *                             @OA\Property(property="sent", type="boolean", example=true),
     *                             @OA\Property(property="delivered", type="boolean", example=false),
     *                             @OA\Property(property="message_id", type="string", example="push_456789123")
     *                         )
     *                     ),
     *                     @OA\Property(property="custom_message", type="string", example="Don't forget your appointment tomorrow at 10:00 AM"),
     *                     @OA\Property(property="recipients", type="array", @OA\Items(type="string"), example={"patient@example.com","+1234567890"}),
     *                     @OA\Property(property="next_reminder_scheduled", type="string", format="date-time", example="2024-01-15T08:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this appointment",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Appointment already cancelled or reminder not needed",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function sendReminder(Appointment $appointment): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($appointment->clinic_id)) {
                return $this->forbiddenResponse('No access to this appointment');
            }

            if ($appointment->status === 'cancelled') {
                return $this->errorResponse('Cannot send reminder for cancelled appointment', null, 422);
            }

            $appointment->sendReminder();

            return $this->successResponse([
                'appointment' => $appointment,
            ], 'Reminder sent successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/appointments/available-slots",
     *     summary="Get available slots",
     *     description="Get available appointment time slots for a specific doctor and date",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date to check availability (YYYY-MM-DD)",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2024-01-15")
     *     ),
     *     @OA\Parameter(
     *         name="appointment_type",
     *         in="query",
     *         description="Type of appointment",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","procedure"}, example="consultation")
     *     ),
     *     @OA\Parameter(
     *         name="duration",
     *         in="query",
     *         description="Appointment duration in minutes",
     *         @OA\Schema(type="integer", example=30)
     *     ),
     *     @OA\Parameter(
     *         name="include_breaks",
     *         in="query",
     *         description="Include break times in results",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available slots retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Available slots retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="doctor", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Dr. John Smith"),
     *                     @OA\Property(property="specialization", type="string", example="Cardiology")
     *                 ),
     *                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="working_hours", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="17:00")
     *                 ),
     *                 @OA\Property(
     *                     property="available_slots",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="time", type="string", example="09:00"),
     *                         @OA\Property(property="datetime", type="string", format="date-time", example="2024-01-15T09:00:00Z"),
     *                         @OA\Property(property="duration", type="integer", example=30),
     *                         @OA\Property(property="is_available", type="boolean", example=true),
     *                         @OA\Property(property="slot_type", type="string", example="regular"),
     *                         @OA\Property(property="notes", type="string", example="Morning slot available")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="unavailable_slots",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="time", type="string", example="10:00"),
     *                         @OA\Property(property="datetime", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                         @OA\Property(property="reason", type="string", example="Already booked"),
     *                         @OA\Property(property="appointment_id", type="integer", example=5)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_available", type="integer", example=12),
     *                 @OA\Property(property="total_unavailable", type="integer", example=8),
     *                 @OA\Property(property="next_available_date", type="string", format="date", example="2024-01-16")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function availableSlots(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:doctors,id',
                'date' => 'required|date|after_or_equal:today',
                'duration' => 'nullable|integer|min:15|max:480',
                'room_id' => 'nullable|integer|exists:rooms,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $doctor = Doctor::findOrFail($request->doctor_id);
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $date = Carbon::parse($request->date);
            $duration = $request->get('duration', 30);
            $roomId = $request->get('room_id');

            $timeSlots = Appointment::getAvailableTimeSlots($doctor->id, $date, $duration, $roomId);

            return $this->successResponse([
                'time_slots' => $timeSlots,
                'doctor' => $doctor,
                'date' => $date->format('Y-m-d'),
                'duration' => $duration,
            ], 'Available time slots retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/appointments/conflicts",
     *     summary="Check conflicts",
     *     description="Check for scheduling conflicts for a proposed appointment",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Doctor ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="appointment_date",
     *         in="query",
     *         description="Proposed appointment date and time",
     *         required=true,
     *         @OA\Schema(type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *     ),
     *     @OA\Parameter(
     *         name="duration",
     *         in="query",
     *         description="Appointment duration in minutes",
     *         @OA\Schema(type="integer", example=30)
     *     ),
     *     @OA\Parameter(
     *         name="exclude_appointment_id",
     *         in="query",
     *         description="Appointment ID to exclude from conflict check (for rescheduling)",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="check_room_availability",
     *         in="query",
     *         description="Also check room availability",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conflict check completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conflict check completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="has_conflicts", type="boolean", example=false),
     *                 @OA\Property(property="conflict_severity", type="string", enum={"none","warning","error"}, example="none"),
     *                 @OA\Property(property="proposed_appointment", type="object",
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="duration", type="integer", example=30),
     *                     @OA\Property(property="end_time", type="string", format="date-time", example="2024-01-15T10:30:00Z")
     *                 ),
     *                 @OA\Property(
     *                     property="conflicts",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="type", type="string", example="doctor_unavailable"),
     *                         @OA\Property(property="severity", type="string", example="error"),
     *                         @OA\Property(property="message", type="string", example="Doctor has another appointment at this time"),
     *                         @OA\Property(property="conflicting_appointment", type="object",
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="patient_name", type="string", example="Jane Doe"),
     *                             @OA\Property(property="start_time", type="string", format="date-time", example="2024-01-15T09:45:00Z"),
     *                             @OA\Property(property="end_time", type="string", format="date-time", example="2024-01-15T10:15:00Z"),
     *                             @OA\Property(property="overlap_minutes", type="integer", example=15)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="doctor_availability", type="object",
     *                     @OA\Property(property="is_available", type="boolean", example=true),
     *                     @OA\Property(property="working_hours", type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="17:00")
     *                     ),
     *                     @OA\Property(property="break_times", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(property="room_availability", type="object",
     *                     @OA\Property(property="is_available", type="boolean", example=true),
     *                     @OA\Property(property="room_id", type="integer", example=1),
     *                     @OA\Property(property="room_name", type="string", example="Room 101")
     *                 ),
     *                 @OA\Property(property="recommendations", type="array", @OA\Items(type="string"), example={"Consider 10:30 AM slot","Room 102 is available"}),
     *                 @OA\Property(property="alternative_slots", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:doctors,id',
                'start_at' => 'required|date',
                'duration' => 'required|integer|min:15|max:480',
                'room_id' => 'nullable|integer|exists:rooms,id',
                'exclude_appointment_id' => 'nullable|integer|exists:appointments,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $doctor = Doctor::findOrFail($request->doctor_id);
            if (!$this->hasClinicAccess($doctor->clinic_id)) {
                return $this->forbiddenResponse('No access to this doctor');
            }

            $startTime = Carbon::parse($request->start_at);
            $duration = $request->duration;
            $roomId = $request->get('room_id');
            $excludeId = $request->get('exclude_appointment_id');

            $isAvailable = Appointment::isTimeSlotAvailable($doctor->id, $startTime, $duration, $roomId, $excludeId);

            return $this->successResponse([
                'is_available' => $isAvailable,
                'conflicts' => !$isAvailable,
            ], 'Conflict check completed');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/appointments/today",
     *     summary="Get today's appointments",
     *     description="Retrieve all appointments scheduled for today",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by appointment status",
     *         @OA\Schema(type="string", enum={"scheduled","confirmed","in_progress","completed","cancelled","no_show"}, example="scheduled")
     *     ),
     *     @OA\Parameter(
     *         name="appointment_type",
     *         in="query",
     *         description="Filter by appointment type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","procedure"}, example="consultation")
     *     ),
     *     @OA\Parameter(
     *         name="include_completed",
     *         in="query",
     *         description="Include completed appointments",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort appointments by",
     *         @OA\Schema(type="string", enum={"time","patient_name","doctor_name","status"}, example="time")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Today's appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Today's appointments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="total_appointments", type="integer", example=12),
     *                 @OA\Property(property="completed_appointments", type="integer", example=8),
     *                 @OA\Property(property="pending_appointments", type="integer", example=4),
     *                 @OA\Property(property="cancelled_appointments", type="integer", example=1),
     *                 @OA\Property(property="no_show_appointments", type="integer", example=0),
     *                 @OA\Property(
     *                     property="appointments",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="patient_id", type="integer", example=1),
     *                         @OA\Property(property="doctor_id", type="integer", example=1),
     *                         @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                         @OA\Property(property="duration", type="integer", example=30),
     *                         @OA\Property(property="status", type="string", example="scheduled"),
     *                         @OA\Property(property="appointment_type", type="string", example="consultation"),
     *                         @OA\Property(property="reason", type="string", example="Regular checkup"),
     *                         @OA\Property(property="notes", type="string", example="Patient prefers morning appointments"),
     *                         @OA\Property(property="check_in_time", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="check_out_time", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="patient", type="object", nullable=true),
     *                         @OA\Property(property="doctor", type="object", nullable=true),
     *                         @OA\Property(property="room", type="object", nullable=true),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="summary",
     *                     type="object",
     *                     @OA\Property(property="morning_appointments", type="integer", example=6),
     *                     @OA\Property(property="afternoon_appointments", type="integer", example=4),
     *                     @OA\Property(property="evening_appointments", type="integer", example=2),
     *                     @OA\Property(property="average_duration", type="number", format="float", example=32.5),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=1200.00)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $appointments = Appointment::where('clinic_id', $currentClinic->id)
                ->today()
                ->with(['patient', 'doctor.user', 'clinic', 'room'])
                ->orderBy('start_at')
                ->get();

            return $this->successResponse([
                'appointments' => $appointments,
                'date' => now()->format('Y-m-d'),
            ], 'Today\'s appointments retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/appointments/upcoming",
     *     summary="Get upcoming appointments",
     *     description="Retrieve upcoming appointments for the next few days",
     *     tags={"Appointments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Number of days to look ahead",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by appointment status",
     *         @OA\Schema(type="string", enum={"scheduled","confirmed","in_progress"}, example="scheduled")
     *     ),
     *     @OA\Parameter(
     *         name="appointment_type",
     *         in="query",
     *         description="Filter by appointment type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","procedure"}, example="consultation")
     *     ),
     *     @OA\Parameter(
     *         name="include_reminders",
     *         in="query",
     *         description="Include reminder information",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Upcoming appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Upcoming appointments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="date_range", type="object",
     *                     @OA\Property(property="start_date", type="string", format="date", example="2024-01-15"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2024-01-22"),
     *                     @OA\Property(property="days_ahead", type="integer", example=7)
     *                 ),
     *                 @OA\Property(property="total_appointments", type="integer", example=25),
     *                 @OA\Property(property="appointments_by_date", type="object",
     *                     @OA\Property(property="2024-01-15", type="integer", example=5),
     *                     @OA\Property(property="2024-01-16", type="integer", example=4),
     *                     @OA\Property(property="2024-01-17", type="integer", example=6),
     *                     @OA\Property(property="2024-01-18", type="integer", example=3),
     *                     @OA\Property(property="2024-01-19", type="integer", example=7)
     *                 ),
     *                 @OA\Property(
     *                     property="appointments",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="patient_id", type="integer", example=1),
     *                         @OA\Property(property="doctor_id", type="integer", example=1),
     *                         @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-16T10:00:00Z"),
     *                         @OA\Property(property="duration", type="integer", example=30),
     *                         @OA\Property(property="status", type="string", example="scheduled"),
     *                         @OA\Property(property="appointment_type", type="string", example="consultation"),
     *                         @OA\Property(property="reason", type="string", example="Follow-up visit"),
     *                         @OA\Property(property="notes", type="string", example="Patient requested morning appointment"),
     *                         @OA\Property(property="days_until_appointment", type="integer", example=1),
     *                         @OA\Property(property="patient", type="object", nullable=true),
     *                         @OA\Property(property="doctor", type="object", nullable=true),
     *                         @OA\Property(property="room", type="object", nullable=true),
     *                         @OA\Property(property="reminder_sent", type="boolean", example=false),
     *                         @OA\Property(property="next_reminder_due", type="string", format="date-time", example="2024-01-15T18:00:00Z"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="summary",
     *                     type="object",
     *                     @OA\Property(property="urgent_appointments", type="integer", example=2),
     *                     @OA\Property(property="follow_up_appointments", type="integer", example=8),
     *                     @OA\Property(property="new_patient_appointments", type="integer", example=3),
     *                     @OA\Property(property="appointments_needing_reminders", type="integer", example=12),
     *                     @OA\Property(property="conflicts_detected", type="integer", example=0)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $days = $request->get('days', 7);
            $appointments = Appointment::where('clinic_id', $currentClinic->id)
                ->upcoming($days)
                ->with(['patient', 'doctor.user', 'clinic', 'room'])
                ->orderBy('start_at')
                ->get();

            return $this->successResponse([
                'appointments' => $appointments,
                'days' => $days,
            ], 'Upcoming appointments retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get calendar events
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'start' => 'required|date',
                'end' => 'required|date|after:start',
                'doctor_id' => 'nullable|integer|exists:doctors,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $query = Appointment::where('clinic_id', $currentClinic->id)
                ->whereBetween('start_at', [$request->start, $request->end])
                ->with(['patient', 'doctor.user', 'room']);

            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            $appointments = $query->get();

            $events = $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient->full_name,
                    'start' => $appointment->start_at->toISOString(),
                    'end' => $appointment->end_at->toISOString(),
                    'status' => $appointment->status,
                    'type' => $appointment->appointment_type,
                    'doctor' => $appointment->doctor->user->name,
                    'room' => $appointment->room->name ?? null,
                    'color' => $this->getStatusColor($appointment->status),
                ];
            });

            return $this->successResponse([
                'events' => $events,
            ], 'Calendar events retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Search appointments
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $query = $request->get('query');
            $limit = $request->get('limit', 20);

            $appointments = Appointment::where('clinic_id', $currentClinic->id)
                ->whereHas('patient', function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->orWhereHas('doctor.user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->with(['patient', 'doctor.user', 'clinic', 'room'])
                ->limit($limit)
                ->get();

            return $this->successResponse([
                'appointments' => $appointments,
            ], 'Search results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get appointment reports
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $reports = [
                'total_appointments' => Appointment::where('clinic_id', $currentClinic->id)->count(),
                'appointments_today' => Appointment::where('clinic_id', $currentClinic->id)->today()->count(),
                'appointments_this_week' => Appointment::where('clinic_id', $currentClinic->id)
                    ->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'appointments_by_status' => Appointment::where('clinic_id', $currentClinic->id)
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
                'appointments_by_type' => Appointment::where('clinic_id', $currentClinic->id)
                    ->selectRaw('appointment_type, COUNT(*) as count')
                    ->groupBy('appointment_type')
                    ->get(),
            ];

            return $this->successResponse([
                'reports' => $reports,
            ], 'Appointment reports retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Export appointments
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $appointments = Appointment::where('clinic_id', $currentClinic->id)
                ->with(['patient', 'doctor.user', 'clinic', 'room'])
                ->get();

            return $this->successResponse([
                'appointments' => $appointments,
                'export_url' => null, // Would be a download URL in real implementation
            ], 'Export data prepared');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get status color for calendar
     */
    private function getStatusColor(string $status): string
    {
        $colors = [
            'scheduled' => '#3b82f6',
            'confirmed' => '#10b981',
            'in_progress' => '#f59e0b',
            'completed' => '#6b7280',
            'cancelled' => '#ef4444',
            'no_show' => '#8b5cf6',
            'rescheduled' => '#06b6d4',
            'waiting' => '#f97316',
            'checked_in' => '#84cc16',
            'checked_out' => '#6366f1',
        ];

        return $colors[$status] ?? '#6b7280';
    }
}
