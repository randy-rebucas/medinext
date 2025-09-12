<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Queue;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="Queue",
 *     type="object",
 *     title="Queue",
 *     description="Queue model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Morning Consultation"),
 *     @OA\Property(property="description", type="string", example="General consultation queue"),
 *     @OA\Property(property="status", type="string", enum={"active", "paused", "closed"}, example="active"),
 *     @OA\Property(property="max_patients", type="integer", example=20),
 *     @OA\Property(property="current_position", type="integer", example=5),
 *     @OA\Property(property="estimated_wait_time", type="integer", example=30),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */



class QueueController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/queues",
     *     summary="Get all queues",
     *     description="Retrieve a paginated list of queues with optional filtering",
     *     operationId="getQueues",
     *     tags={"Queues"},
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
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by queue status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "paused", "closed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Queues retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Queues retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="pagination", type="object")
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
            $this->requirePermission('queue.view');

            $currentClinic = $this->getCurrentClinic();
            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $query = Queue::with(['doctor', 'clinic'])
                ->where('clinic_id', $currentClinic->id);

            // Apply filters
            if ($request->has('doctor_id')) {
                $query->where('doctor_id', $request->get('doctor_id'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            $perPage = $request->get('per_page', 15);
            $queues = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse($queues, 'Queues retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve queues: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/queues",
     *     summary="Create a new queue",
     *     description="Create a new queue",
     *     operationId="createQueue",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"doctor_id", "name"},
     *             @OA\Property(property="doctor_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Morning Consultation"),
     *             @OA\Property(property="description", type="string", example="General consultation queue"),
     *             @OA\Property(property="max_patients", type="integer", example=20),
     *             @OA\Property(property="estimated_wait_time", type="integer", example=30)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Queue created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Queue created successfully"),
     *             @OA\Property(property="data", type="object")
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
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|exists:doctors,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'max_patients' => 'nullable|integer|min:1|max:100',
                'estimated_wait_time' => 'nullable|integer|min:1|max:480'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $queueData = $request->all();
            $queueData['clinic_id'] = Auth::user()->current_clinic_id ?? 1;
            $queueData['status'] = 'active';
            $queueData['current_position'] = 0;

            $queue = Queue::create($queueData);
            $queue->load(['doctor', 'clinic']);

            return $this->successResponse($queue, 'Queue created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create queue: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/queues/{id}",
     *     summary="Get a specific queue",
     *     description="Retrieve a specific queue by ID",
     *     operationId="getQueue",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Queue ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Queue retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Queue retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Queue not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $queue = Queue::with(['doctor', 'clinic'])->findOrFail($id);
            return $this->successResponse($queue, 'Queue retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Queue not found', null, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/queues/{id}",
     *     summary="Update a queue",
     *     description="Update an existing queue",
     *     operationId="updateQueue",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Queue ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Morning Consultation"),
     *             @OA\Property(property="description", type="string", example="General consultation queue"),
     *             @OA\Property(property="status", type="string", enum={"active", "paused", "closed"}, example="active"),
     *             @OA\Property(property="max_patients", type="integer", example=20),
     *             @OA\Property(property="estimated_wait_time", type="integer", example=30)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Queue updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Queue updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Queue not found",
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
            $queue = Queue::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:500',
                'status' => 'sometimes|in:active,paused,closed',
                'max_patients' => 'nullable|integer|min:1|max:100',
                'estimated_wait_time' => 'nullable|integer|min:1|max:480'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $queue->update($request->all());
            $queue->load(['doctor', 'clinic']);

            return $this->successResponse($queue, 'Queue updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update queue: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/queues/{id}",
     *     summary="Delete a queue",
     *     description="Delete a queue (soft delete)",
     *     operationId="deleteQueue",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Queue ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Queue deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Queue deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Queue not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $queue = Queue::findOrFail($id);
            $queue->delete();

            return $this->successResponse(null, 'Queue deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete queue: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/queues/{id}/patients",
     *     summary="Get queue patients",
     *     description="Retrieve all patients in a specific queue",
     *     operationId="getQueuePatients",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Queue ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Queue patients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Queue patients retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Patient")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Queue not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function patients($id): JsonResponse
    {
        try {
            $queue = Queue::findOrFail($id);
            $patients = $queue->patients()->orderBy('queue_position')->get();

            return $this->successResponse($patients, 'Queue patients retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve queue patients: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/queues/{id}/add-patient",
     *     summary="Add patient to queue",
     *     description="Add a patient to the queue",
     *     operationId="addPatientToQueue",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Queue ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id"},
     *             @OA\Property(property="patient_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient added to queue successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient added to queue successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="queue_position", type="integer", example=5),
     *                 @OA\Property(property="estimated_wait_time", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Queue or patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function addPatient(Request $request, $id): JsonResponse
    {
        try {
            $queue = Queue::findOrFail($id);
            $patient = Patient::findOrFail($request->get('patient_id'));

            // Implementation for adding patient to queue would go here
            $queuePosition = $queue->current_position + 1;
            $estimatedWaitTime = $queuePosition * $queue->estimated_wait_time;

            $result = [
                'queue_position' => $queuePosition,
                'estimated_wait_time' => $estimatedWaitTime
            ];

            return $this->successResponse($result, 'Patient added to queue successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add patient to queue: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/queues/active",
     *     summary="Get active queues",
     *     description="Retrieve all active queues",
     *     operationId="getActiveQueues",
     *     tags={"Queues"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active queues retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Active queues retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function active(): JsonResponse
    {
        try {
            $queues = Queue::with(['doctor', 'clinic'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($queues, 'Active queues retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve active queues: ' . $e->getMessage());
        }
    }
}
