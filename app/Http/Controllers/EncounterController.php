<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Validator;

class EncounterController extends Controller
{
    /**
     * Display the encounters management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Encounters Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/encounters', [
                'encounters' => [],
                'patients' => [],
                'doctors' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get encounters for this clinic
        $encounters = $this->getEncounters($clinicId);

        // Get patients for dropdown
        $patients = $this->getPatients($clinicId);

        // Get doctors for dropdown
        $doctors = $this->getDoctors($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/encounters', [
            'encounters' => $encounters,
            'patients' => $patients,
            'doctors' => $doctors,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created encounter
     */
    public function store(Request $request)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|before_or_equal:today',
            'type' => 'required|string|in:consultation,examination,procedure,follow_up,emergency',
            'chief_complaint' => 'required|string|max:1000|regex:/^[a-zA-Z0-9\s\-\'\.\,\!\?]+$/',
            'history_of_present_illness' => 'nullable|string|max:2000',
            'physical_examination' => 'nullable|string|max:2000',
            'assessment' => 'nullable|string|max:2000',
            'plan' => 'nullable|string|max:2000',
            'vital_signs' => 'nullable|array',
            'vital_signs.*.parameter' => 'required|string|max:100',
            'vital_signs.*.value' => 'required|string|max:100',
            'vital_signs.*.unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,completed,cancelled',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'date.before_or_equal' => 'Encounter date cannot be in the future.',
            'type.in' => 'Invalid encounter type.',
            'chief_complaint.regex' => 'Chief complaint can only contain letters, numbers, spaces, hyphens, apostrophes, periods, commas, exclamation marks, and question marks.',
            'vital_signs.*.parameter.required' => 'Vital sign parameter is required.',
            'vital_signs.*.value.required' => 'Vital sign value is required.',
            'status.in' => 'Invalid encounter status.',
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
            $this->logWebRequest('Create Encounter', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated encounter creation attempt');
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

            // Create encounter
            $encounter = Encounter::create([
                'clinic_id' => $clinicId,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'date' => $request->date,
                'type' => $request->type,
                'chief_complaint' => $request->chief_complaint,
                'history_of_present_illness' => $request->history_of_present_illness,
                'physical_examination' => $request->physical_examination,
                'assessment' => $request->assessment,
                'plan' => $request->plan,
                'vital_signs' => $request->vital_signs ?? [],
                'notes' => $request->notes,
                'status' => $request->status,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Encounter created successfully',
                'encounter' => $this->getEncounter($encounter->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'EncounterController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create encounter. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified encounter
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
            'type' => 'required|string|in:consultation,examination,procedure,follow_up,emergency',
            'chief_complaint' => 'required|string|max:1000|regex:/^[a-zA-Z0-9\s\-\'\.\,\!\?]+$/',
            'history_of_present_illness' => 'nullable|string|max:2000',
            'physical_examination' => 'nullable|string|max:2000',
            'assessment' => 'nullable|string|max:2000',
            'plan' => 'nullable|string|max:2000',
            'vital_signs' => 'nullable|array',
            'vital_signs.*.parameter' => 'required|string|max:100',
            'vital_signs.*.value' => 'required|string|max:100',
            'vital_signs.*.unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,completed,cancelled',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'doctor_id.exists' => 'Selected doctor does not exist.',
            'type.in' => 'Invalid encounter type.',
            'chief_complaint.regex' => 'Chief complaint can only contain letters, numbers, spaces, hyphens, apostrophes, periods, commas, exclamation marks, and question marks.',
            'vital_signs.*.parameter.required' => 'Vital sign parameter is required.',
            'vital_signs.*.value.required' => 'Vital sign value is required.',
            'status.in' => 'Invalid encounter status.',
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
            $encounter = Encounter::findOrFail($id);

            // Update encounter
            $encounter->update([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'date' => $request->date,
                'type' => $request->type,
                'chief_complaint' => $request->chief_complaint,
                'history_of_present_illness' => $request->history_of_present_illness,
                'physical_examination' => $request->physical_examination,
                'assessment' => $request->assessment,
                'plan' => $request->plan,
                'vital_signs' => $request->vital_signs ?? [],
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Encounter updated successfully',
                'encounter' => $this->getEncounter($encounter->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'EncounterController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update encounter. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified encounter
     */
    public function destroy($id)
    {
        try {
            $encounter = Encounter::findOrFail($id);
            $encounter->delete();

            return response()->json([
                'success' => true,
                'message' => 'Encounter deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'EncounterController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete encounter. Please try again.'
            ], 500);
        }
    }

    /**
     * Get encounter details
     */
    public function show($id)
    {
        try {
            $encounter = $this->getEncounter($id);

            if (!$encounter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Encounter not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Encounter retrieved successfully',
                'encounter' => $encounter
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'EncounterController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve encounter. Please try again.'
            ], 500);
        }
    }

    /**
     * Get encounters for a clinic with caching
     */
    private function getEncounters($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('encounters', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return Encounter::where('clinic_id', $clinicId)
                ->with(['patient:id,first_name,last_name', 'doctor.user:id,name'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($encounter) {
                    return [
                        'id' => $encounter->id,
                        'date' => $encounter->date,
                        'type' => $encounter->type,
                        'chief_complaint' => $encounter->chief_complaint,
                        'status' => $encounter->status,
                        'patient_name' => $encounter->patient->first_name . ' ' . $encounter->patient->last_name,
                        'patient_id' => $encounter->patient_id,
                        'doctor_name' => $encounter->doctor->user->name,
                        'doctor_id' => $encounter->doctor_id,
                        'vital_signs_count' => count($encounter->vital_signs),
                        'notes' => $encounter->notes,
                        'created_at' => $encounter->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $encounter->updated_at->format('Y-m-d H:i:s'),
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
     * Get a single encounter
     */
    private function getEncounter($encounterId)
    {
        $encounter = Encounter::with(['patient', 'doctor.user'])->findOrFail($encounterId);

        return [
            'id' => $encounter->id,
            'date' => $encounter->date,
            'type' => $encounter->type,
            'chief_complaint' => $encounter->chief_complaint,
            'history_of_present_illness' => $encounter->history_of_present_illness,
            'physical_examination' => $encounter->physical_examination,
            'assessment' => $encounter->assessment,
            'plan' => $encounter->plan,
            'vital_signs' => $encounter->vital_signs,
            'notes' => $encounter->notes,
            'status' => $encounter->status,
            'patient' => [
                'id' => $encounter->patient->id,
                'name' => $encounter->patient->first_name . ' ' . $encounter->patient->last_name,
            ],
            'doctor' => [
                'id' => $encounter->doctor->id,
                'name' => $encounter->doctor->user->name,
            ],
            'created_at' => $encounter->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $encounter->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_encounters', 'view_encounters', 'create_encounters',
                'edit_encounters', 'delete_encounters'
            ],
            'admin' => [
                'manage_encounters', 'view_encounters', 'create_encounters',
                'edit_encounters', 'delete_encounters'
            ],
            'doctor' => [
                'view_encounters', 'create_encounters', 'edit_encounters'
            ],
            'receptionist' => [
                'view_encounters', 'create_encounters'
            ],
            'patient' => [
                'view_encounters'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
