<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Queue;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Validator;

class QueueController extends Controller
{
    /**
     * Display the queue management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Queue Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/queues', [
                'queues' => [],
                'doctors' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get queues for this clinic
        $queues = $this->getQueues($clinicId);

        // Get doctors for dropdown
        $doctors = $this->getDoctors($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/queues', [
            'queues' => $queues,
            'doctors' => $doctors,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created queue
     */
    public function store(Request $request)
    {
        $rules = [
            'doctor_id' => 'required|exists:doctors,id',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.]+$/',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,paused,closed',
            'max_patients' => 'required|integer|min:1|max:1000',
            'estimated_wait_time' => 'nullable|integer|min:0|max:1440', // 24 hours in minutes
        ];

        $messages = [
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'name.regex' => 'Queue name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'status.in' => 'Invalid queue status.',
            'max_patients.min' => 'Maximum patients must be at least 1.',
            'max_patients.max' => 'Maximum patients cannot exceed 1000.',
            'estimated_wait_time.max' => 'Estimated wait time cannot exceed 1440 minutes (24 hours).',
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
            $this->logWebRequest('Create Queue', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated queue creation attempt');
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

            // Create queue
            $queue = Queue::create([
                'clinic_id' => $clinicId,
                'doctor_id' => $request->doctor_id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'max_patients' => $request->max_patients,
                'current_position' => 0,
                'estimated_wait_time' => $request->estimated_wait_time ?? 30,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Queue created successfully',
                'queue' => $this->getQueue($queue->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'QueueController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create queue. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified queue
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'doctor_id' => 'required|exists:doctors,id',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.]+$/',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,paused,closed',
            'max_patients' => 'required|integer|min:1|max:1000',
            'estimated_wait_time' => 'nullable|integer|min:0|max:1440',
        ];

        $messages = [
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'name.regex' => 'Queue name can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'status.in' => 'Invalid queue status.',
            'max_patients.min' => 'Maximum patients must be at least 1.',
            'max_patients.max' => 'Maximum patients cannot exceed 1000.',
            'estimated_wait_time.max' => 'Estimated wait time cannot exceed 1440 minutes (24 hours).',
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
            $queue = Queue::findOrFail($id);

            // Update queue
            $queue->update([
                'doctor_id' => $request->doctor_id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'max_patients' => $request->max_patients,
                'estimated_wait_time' => $request->estimated_wait_time ?? 30,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Queue updated successfully',
                'queue' => $this->getQueue($queue->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'QueueController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update queue. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified queue
     */
    public function destroy($id)
    {
        try {
            $queue = Queue::findOrFail($id);
            $queue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Queue deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'QueueController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete queue. Please try again.'
            ], 500);
        }
    }

    /**
     * Get queue details
     */
    public function show($id)
    {
        try {
            $queue = $this->getQueue($id);

            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Queue retrieved successfully',
                'queue' => $queue
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'QueueController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve queue. Please try again.'
            ], 500);
        }
    }

    /**
     * Add patient to queue
     */
    public function addPatient(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'priority' => 'nullable|string|in:normal,urgent,emergency',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'priority.in' => 'Invalid priority level.',
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
            $queue = Queue::findOrFail($id);

            // Check if queue is active
            if ($queue->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add patients to inactive queue'
                ], 422);
            }

            // Check if queue is full
            if ($queue->current_position >= $queue->max_patients) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue is full'
                ], 422);
            }

            // Add patient to queue
            $queue->patients()->attach($request->patient_id, [
                'priority' => $request->priority ?? 'normal',
                'added_at' => now(),
                'position' => $queue->current_position + 1,
            ]);

            // Update current position
            $queue->increment('current_position');

            return response()->json([
                'success' => true,
                'message' => 'Patient added to queue successfully',
                'queue' => $this->getQueue($queue->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'QueueController::addPatient');
            return response()->json([
                'success' => false,
                'message' => 'Failed to add patient to queue. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove patient from queue
     */
    public function removePatient(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
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
            $queue = Queue::findOrFail($id);

            // Remove patient from queue
            $queue->patients()->detach($request->patient_id);

            // Update current position
            $queue->decrement('current_position');

            return response()->json([
                'success' => true,
                'message' => 'Patient removed from queue successfully',
                'queue' => $this->getQueue($queue->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'QueueController::removePatient');
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove patient from queue. Please try again.'
            ], 500);
        }
    }

    /**
     * Get queues for a clinic with caching
     */
    private function getQueues($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('queues', $clinicId);
        
        return $this->remember($cacheKey, 15, function () use ($clinicId) {
            return Queue::where('clinic_id', $clinicId)
                ->with(['doctor.user:id,name'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'name' => $queue->name,
                        'description' => $queue->description,
                        'status' => $queue->status,
                        'max_patients' => $queue->max_patients,
                        'current_position' => $queue->current_position,
                        'estimated_wait_time' => $queue->estimated_wait_time,
                        'doctor_name' => $queue->doctor->user->name,
                        'doctor_id' => $queue->doctor_id,
                        'patients_count' => $queue->patients()->count(),
                        'created_at' => $queue->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $queue->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
        });
    }

    /**
     * Get doctors for dropdown
     */
    private function getDoctors($clinicId)
    {
        return Doctor::where('clinic_id', $clinicId)
            ->with(['user:id,name'])
            ->select('id', 'user_id')
            ->orderBy('user_id')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                ];
            });
    }

    /**
     * Get a single queue
     */
    private function getQueue($queueId)
    {
        $queue = Queue::with(['doctor.user', 'patients'])->findOrFail($queueId);

        return [
            'id' => $queue->id,
            'name' => $queue->name,
            'description' => $queue->description,
            'status' => $queue->status,
            'max_patients' => $queue->max_patients,
            'current_position' => $queue->current_position,
            'estimated_wait_time' => $queue->estimated_wait_time,
            'doctor' => [
                'id' => $queue->doctor->id,
                'name' => $queue->doctor->user->name,
            ],
            'patients' => $queue->patients->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'priority' => $patient->pivot->priority,
                    'added_at' => $patient->pivot->added_at,
                    'position' => $patient->pivot->position,
                ];
            }),
            'created_at' => $queue->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $queue->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_queues', 'view_queues', 'create_queues', 'edit_queues', 'delete_queues',
                'add_patients_to_queue', 'remove_patients_from_queue'
            ],
            'admin' => [
                'manage_queues', 'view_queues', 'create_queues', 'edit_queues', 'delete_queues',
                'add_patients_to_queue', 'remove_patients_from_queue'
            ],
            'doctor' => [
                'view_queues', 'create_queues', 'edit_queues', 'add_patients_to_queue', 'remove_patients_from_queue'
            ],
            'receptionist' => [
                'view_queues', 'create_queues', 'edit_queues', 'add_patients_to_queue', 'remove_patients_from_queue'
            ],
            'patient' => [
                'view_queues'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
