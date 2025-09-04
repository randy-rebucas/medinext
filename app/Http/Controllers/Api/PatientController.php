<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PatientController extends BaseController
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'first_name', 'last_name', 'dob', 'created_at', 'updated_at'
            ]);

            $query = Patient::where('clinic_id', $currentClinic->id)
                ->with(['clinic']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            // Filter by sex
            if ($request->has('sex')) {
                $query->where('sex', $request->get('sex'));
            }

            // Filter by age range
            if ($request->has('age_min') || $request->has('age_max')) {
                $ageMin = $request->get('age_min');
                $ageMax = $request->get('age_max');
                
                if ($ageMin) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?', [$ageMin]);
                }
                if ($ageMax) {
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?', [$ageMax]);
                }
            }

            // Filter by date range
            if ($request->has('date_from') || $request->has('date_to')) {
                if ($request->has('date_from')) {
                    $query->where('created_at', '>=', $request->get('date_from'));
                }
                if ($request->has('date_to')) {
                    $query->where('created_at', '<=', $request->get('date_to'));
                }
            }

            $patients = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($patients, 'Patients retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'dob' => 'required|date|before:today',
                'sex' => 'required|in:male,female,other',
                'contact' => 'nullable|array',
                'contact.phone' => 'nullable|string|max:20',
                'contact.email' => 'nullable|email|max:255',
                'contact.address' => 'nullable|string|max:500',
                'allergies' => 'nullable|array',
                'consents' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();
            $data['clinic_id'] = $currentClinic->id;
            $data['code'] = $this->generatePatientCode($currentClinic->id);

            $patient = Patient::create($data);
            $patient->load(['clinic']);

            return $this->successResponse([
                'patient' => $patient,
            ], 'Patient created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display the specified patient
     */
    public function show(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            $patient->load([
                'clinic',
                'encounters' => function ($query) {
                    $query->latest()->limit(10);
                },
                'appointments' => function ($query) {
                    $query->latest()->limit(10);
                },
                'prescriptions' => function ($query) {
                    $query->latest()->limit(10);
                },
                'labResults' => function ($query) {
                    $query->latest()->limit(10);
                },
                'fileAssets'
            ]);

            return $this->successResponse([
                'patient' => $patient,
                'statistics' => [
                    'total_encounters' => $patient->encounters()->count(),
                    'total_appointments' => $patient->appointments()->count(),
                    'total_prescriptions' => $patient->prescriptions()->count(),
                    'total_lab_results' => $patient->labResults()->count(),
                    'total_files' => $patient->fileAssets()->count(),
                ]
            ], 'Patient retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'dob' => 'sometimes|required|date|before:today',
                'sex' => 'sometimes|required|in:male,female,other',
                'contact' => 'nullable|array',
                'contact.phone' => 'nullable|string|max:20',
                'contact.email' => 'nullable|email|max:255',
                'contact.address' => 'nullable|string|max:500',
                'allergies' => 'nullable|array',
                'consents' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $patient->update($validator->validated());
            $patient->load(['clinic']);

            return $this->successResponse([
                'patient' => $patient,
            ], 'Patient updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified patient
     */
    public function destroy(Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            // Check if patient has any related records
            $hasRecords = $patient->encounters()->exists() || 
                         $patient->appointments()->exists() || 
                         $patient->prescriptions()->exists();

            if ($hasRecords) {
                return $this->errorResponse('Cannot delete patient with existing medical records', null, 422);
            }

            $patient->delete();

            return $this->successResponse(null, 'Patient deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient appointments
     */
    public function appointments(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $appointments = $patient->appointments()
                ->with(['doctor.user', 'clinic', 'room'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($appointments, 'Patient appointments retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient encounters
     */
    public function encounters(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $encounters = $patient->encounters()
                ->with(['doctor.user', 'clinic'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($encounters, 'Patient encounters retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient prescriptions
     */
    public function prescriptions(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $prescriptions = $patient->prescriptions()
                ->with(['doctor.user', 'clinic', 'items'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($prescriptions, 'Patient prescriptions retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient lab results
     */
    public function labResults(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $labResults = $patient->labResults()
                ->with(['doctor.user', 'clinic', 'encounter'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($labResults, 'Patient lab results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient medical history
     */
    public function medicalHistory(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            $history = [
                'encounters' => $patient->encounters()
                    ->with(['doctor.user'])
                    ->latest()
                    ->limit(20)
                    ->get(),
                'appointments' => $patient->appointments()
                    ->with(['doctor.user'])
                    ->latest()
                    ->limit(20)
                    ->get(),
                'prescriptions' => $patient->prescriptions()
                    ->with(['doctor.user', 'items'])
                    ->latest()
                    ->limit(20)
                    ->get(),
                'lab_results' => $patient->labResults()
                    ->with(['doctor.user'])
                    ->latest()
                    ->limit(20)
                    ->get(),
            ];

            return $this->successResponse([
                'medical_history' => $history,
            ], 'Medical history retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient file assets
     */
    public function fileAssets(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $fileAssets = $patient->fileAssets()
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($fileAssets, 'Patient files retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Upload file for patient
     */
    public function uploadFile(Request $request, Patient $patient): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($patient->clinic_id)) {
                return $this->forbiddenResponse('No access to this patient');
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
                'category' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $file = $request->file('file');
            $path = $file->store('patients/' . $patient->id, 'private');

            $fileAsset = $patient->fileAssets()->create([
                'clinic_id' => $patient->clinic_id,
                'url' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'checksum' => md5_file($file->getPathname()),
                'category' => $request->category,
                'description' => $request->description,
                'file_name' => $file->hashName(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            return $this->successResponse([
                'file_asset' => $fileAsset,
            ], 'File uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Search patients
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

            $patients = Patient::where('clinic_id', $currentClinic->id)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->limit($limit)
                ->get(['id', 'code', 'first_name', 'last_name', 'dob', 'sex']);

            return $this->successResponse([
                'patients' => $patients,
            ], 'Search results retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get recent patients
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $limit = $request->get('limit', 10);

            $patients = Patient::where('clinic_id', $currentClinic->id)
                ->latest()
                ->limit($limit)
                ->get(['id', 'code', 'first_name', 'last_name', 'dob', 'sex', 'created_at']);

            return $this->successResponse([
                'patients' => $patients,
            ], 'Recent patients retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Export patients
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            // This would typically generate a CSV or Excel file
            // For now, return the data that would be exported
            $patients = Patient::where('clinic_id', $currentClinic->id)
                ->with(['clinic'])
                ->get();

            return $this->successResponse([
                'patients' => $patients,
                'export_url' => null, // Would be a download URL in real implementation
            ], 'Export data prepared');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get patient reports
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $reports = [
                'total_patients' => Patient::where('clinic_id', $currentClinic->id)->count(),
                'new_patients_this_month' => Patient::where('clinic_id', $currentClinic->id)
                    ->whereMonth('created_at', now()->month)
                    ->count(),
                'patients_by_sex' => Patient::where('clinic_id', $currentClinic->id)
                    ->selectRaw('sex, COUNT(*) as count')
                    ->groupBy('sex')
                    ->get(),
                'patients_by_age_group' => Patient::where('clinic_id', $currentClinic->id)
                    ->selectRaw('
                        CASE 
                            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN "Under 18"
                            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 65 THEN "18-65"
                            ELSE "Over 65"
                        END as age_group,
                        COUNT(*) as count
                    ')
                    ->groupBy('age_group')
                    ->get(),
            ];

            return $this->successResponse([
                'reports' => $reports,
            ], 'Patient reports retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Generate unique patient code
     */
    private function generatePatientCode(int $clinicId): string
    {
        $clinic = Clinic::find($clinicId);
        $clinicCode = $clinic ? strtoupper(substr($clinic->name, 0, 3)) : 'CLN';
        
        $date = now()->format('Ymd');
        $sequence = Patient::where('clinic_id', $clinicId)
            ->whereDate('created_at', today())
            ->count() + 1;

        return sprintf('PT%s%s%04d', $clinicCode, $date, $sequence);
    }
}
