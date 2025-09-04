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

class AppointmentController extends BaseController
{
    /**
     * Display a listing of appointments
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
     * Store a newly created appointment
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
     * Display the specified appointment
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
     * Check in patient
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
     * Check out patient
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
     * Cancel appointment
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
     * Reschedule appointment
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
     * Send reminder
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
     * Get available time slots
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
     * Check for conflicts
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
     * Get today's appointments
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
     * Get upcoming appointments
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
