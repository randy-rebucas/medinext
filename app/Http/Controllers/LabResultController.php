<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Encounter;
use Illuminate\Support\Facades\Validator;

class LabResultController extends Controller
{
    /**
     * Display the lab results management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Lab Results Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/lab-results', [
                'labResults' => [],
                'patients' => [],
                'doctors' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get lab results for this clinic
        $labResults = $this->getLabResults($clinicId);

        // Get patients for dropdown
        $patients = $this->getPatients($clinicId);

        // Get doctors for dropdown
        $doctors = $this->getDoctors($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/lab-results', [
            'labResults' => $labResults,
            'patients' => $patients,
            'doctors' => $doctors,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created lab result
     */
    public function store(Request $request)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'doctor_id' => 'required|exists:doctors,id',
            'ordered_by_doctor_id' => 'nullable|exists:doctors,id',
            'test_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.&]+$/',
            'test_code' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\-\s]+$/',
            'test_date' => 'required|date|before_or_equal:today',
            'result_date' => 'nullable|date|after_or_equal:test_date',
            'status' => 'required|string|in:pending,completed,abnormal,cancelled',
            'results' => 'required|array',
            'results.*.parameter' => 'required|string|max:255',
            'results.*.value' => 'required|string|max:255',
            'results.*.unit' => 'nullable|string|max:50',
            'results.*.reference_range' => 'nullable|string|max:255',
            'results.*.flag' => 'nullable|string|in:normal,high,low,critical',
            'notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'encounter_id.exists' => 'Selected encounter does not exist.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'ordered_by_doctor_id.exists' => 'Selected ordering doctor does not exist.',
            'test_name.regex' => 'Test name can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and ampersands.',
            'test_code.regex' => 'Test code can only contain letters, numbers, hyphens, and spaces.',
            'test_date.before_or_equal' => 'Test date cannot be in the future.',
            'result_date.after_or_equal' => 'Result date must be on or after test date.',
            'status.in' => 'Invalid test status.',
            'results.required' => 'At least one test result is required.',
            'results.*.parameter.required' => 'Test parameter is required.',
            'results.*.value.required' => 'Test value is required.',
            'results.*.flag.in' => 'Invalid result flag.',
            'attachments.*.max' => 'Attachment file size cannot exceed 10MB.',
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
            $this->logWebRequest('Create Lab Result', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated lab result creation attempt');
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

            // Create lab result
            $labResult = LabResult::create([
                'clinic_id' => $clinicId,
                'patient_id' => $request->patient_id,
                'encounter_id' => $request->encounter_id,
                'doctor_id' => $request->doctor_id,
                'ordered_by_doctor_id' => $request->ordered_by_doctor_id,
                'test_name' => $request->test_name,
                'test_code' => $request->test_code,
                'test_date' => $request->test_date,
                'result_date' => $request->result_date,
                'status' => $request->status,
                'results' => $request->results,
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            // Handle file attachments if any
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('lab-results', $filename, 'public');
                    
                    $labResult->attachments()->create([
                        'original_name' => $file->getClientOriginalName(),
                        'filename' => $filename,
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Lab result created successfully',
                'labResult' => $this->getLabResult($labResult->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'LabResultController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lab result. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified lab result
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'doctor_id' => 'required|exists:doctors,id',
            'ordered_by_doctor_id' => 'nullable|exists:doctors,id',
            'test_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.&]+$/',
            'test_code' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\-\s]+$/',
            'test_date' => 'required|date',
            'result_date' => 'nullable|date|after_or_equal:test_date',
            'status' => 'required|string|in:pending,completed,abnormal,cancelled',
            'results' => 'required|array',
            'results.*.parameter' => 'required|string|max:255',
            'results.*.value' => 'required|string|max:255',
            'results.*.unit' => 'nullable|string|max:50',
            'results.*.reference_range' => 'nullable|string|max:255',
            'results.*.flag' => 'nullable|string|in:normal,high,low,critical',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'encounter_id.exists' => 'Selected encounter does not exist.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'ordered_by_doctor_id.exists' => 'Selected ordering doctor does not exist.',
            'test_name.regex' => 'Test name can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and ampersands.',
            'test_code.regex' => 'Test code can only contain letters, numbers, hyphens, and spaces.',
            'result_date.after_or_equal' => 'Result date must be on or after test date.',
            'status.in' => 'Invalid test status.',
            'results.required' => 'At least one test result is required.',
            'results.*.parameter.required' => 'Test parameter is required.',
            'results.*.value.required' => 'Test value is required.',
            'results.*.flag.in' => 'Invalid result flag.',
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
            $labResult = LabResult::findOrFail($id);

            // Update lab result
            $labResult->update([
                'patient_id' => $request->patient_id,
                'encounter_id' => $request->encounter_id,
                'doctor_id' => $request->doctor_id,
                'ordered_by_doctor_id' => $request->ordered_by_doctor_id,
                'test_name' => $request->test_name,
                'test_code' => $request->test_code,
                'test_date' => $request->test_date,
                'result_date' => $request->result_date,
                'status' => $request->status,
                'results' => $request->results,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lab result updated successfully',
                'labResult' => $this->getLabResult($labResult->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'LabResultController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lab result. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified lab result
     */
    public function destroy($id)
    {
        try {
            $labResult = LabResult::findOrFail($id);

            // Delete attachments
            foreach ($labResult->attachments as $attachment) {
                if (file_exists(storage_path('app/public/' . $attachment->file_path))) {
                    unlink(storage_path('app/public/' . $attachment->file_path));
                }
                $attachment->delete();
            }

            // Delete lab result
            $labResult->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lab result deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'LabResultController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lab result. Please try again.'
            ], 500);
        }
    }

    /**
     * Get lab result details
     */
    public function show($id)
    {
        try {
            $labResult = $this->getLabResult($id);

            if (!$labResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lab result not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lab result retrieved successfully',
                'labResult' => $labResult
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'LabResultController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve lab result. Please try again.'
            ], 500);
        }
    }

    /**
     * Get lab results for a clinic with caching
     */
    private function getLabResults($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('lab_results', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return LabResult::where('clinic_id', $clinicId)
                ->with(['patient:id,first_name,last_name', 'doctor.user:id,name', 'encounter:id,date'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($labResult) {
                    return [
                        'id' => $labResult->id,
                        'test_name' => $labResult->test_name,
                        'test_code' => $labResult->test_code,
                        'test_date' => $labResult->test_date,
                        'result_date' => $labResult->result_date,
                        'status' => $labResult->status,
                        'results_count' => count($labResult->results),
                        'patient_name' => $labResult->patient->first_name . ' ' . $labResult->patient->last_name,
                        'patient_id' => $labResult->patient_id,
                        'doctor_name' => $labResult->doctor->user->name,
                        'doctor_id' => $labResult->doctor_id,
                        'encounter_id' => $labResult->encounter_id,
                        'notes' => $labResult->notes,
                        'created_at' => $labResult->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $labResult->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
        });
    }

    /**
     * Get patients for dropdown
     */
    private function getPatients($clinicId)
    {
        return Patient::where('clinic_id', $clinicId)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                ];
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
     * Get a single lab result
     */
    private function getLabResult($labResultId)
    {
        $labResult = LabResult::with(['patient', 'doctor.user', 'encounter', 'attachments'])->findOrFail($labResultId);

        return [
            'id' => $labResult->id,
            'test_name' => $labResult->test_name,
            'test_code' => $labResult->test_code,
            'test_date' => $labResult->test_date,
            'result_date' => $labResult->result_date,
            'status' => $labResult->status,
            'results' => $labResult->results,
            'notes' => $labResult->notes,
            'patient' => [
                'id' => $labResult->patient->id,
                'name' => $labResult->patient->first_name . ' ' . $labResult->patient->last_name,
            ],
            'doctor' => [
                'id' => $labResult->doctor->id,
                'name' => $labResult->doctor->user->name,
            ],
            'encounter' => $labResult->encounter ? [
                'id' => $labResult->encounter->id,
                'date' => $labResult->encounter->date,
            ] : null,
            'attachments' => $labResult->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'filename' => $attachment->filename,
                    'file_size' => $attachment->file_size,
                    'mime_type' => $attachment->mime_type,
                ];
            }),
            'created_at' => $labResult->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $labResult->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_lab_results', 'view_lab_results', 'create_lab_results',
                'edit_lab_results', 'delete_lab_results'
            ],
            'admin' => [
                'manage_lab_results', 'view_lab_results', 'create_lab_results',
                'edit_lab_results', 'delete_lab_results'
            ],
            'doctor' => [
                'view_lab_results', 'create_lab_results', 'edit_lab_results'
            ],
            'receptionist' => [
                'view_lab_results', 'create_lab_results'
            ],
            'patient' => [
                'view_lab_results'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
