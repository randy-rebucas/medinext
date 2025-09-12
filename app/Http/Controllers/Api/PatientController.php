<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;




class PatientController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/patients",
     *     summary="Get all patients",
     *     description="Retrieve a paginated list of patients for the current clinic",
     *     tags={"Patients"},
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
     *         name="search",
     *         in="query",
     *         description="Search by name or patient code",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="sex",
     *         in="query",
     *         description="Filter by sex",
     *         @OA\Schema(type="string", enum={"male","female","other"})
     *     ),
     *     @OA\Parameter(
     *         name="age_min",
     *         in="query",
     *         description="Minimum age",
     *         @OA\Schema(type="integer", example=18)
     *     ),
     *     @OA\Parameter(
     *         name="age_max",
     *         in="query",
     *         description="Maximum age",
     *         @OA\Schema(type="integer", example=65)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","first_name","last_name","dob","created_at","updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patients retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(type="object")
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
     * @OA\Post(
     *     path="/api/v1/patients",
     *     summary="Create a new patient",
     *     description="Create a new patient record in the current clinic",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","dob","sex"},
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="dob", type="string", format="date", example="1990-01-15"),
     *             @OA\Property(property="sex", type="string", enum={"male","female","other"}, example="female"),
     *             @OA\Property(
     *                 property="contact",
     *                 type="object",
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                 @OA\Property(property="address", type="string", example="123 Main St, City, State")
     *             ),
     *             @OA\Property(property="allergies", type="array", @OA\Items(type="string"), example={"Penicillin", "Shellfish"}),
     *             @OA\Property(property="consents", type="array", @OA\Items(type="string"), example={"Treatment", "Data Sharing"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Patient created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient", type="object")
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}",
     *     summary="Get patient details",
     *     description="Retrieve detailed information about a specific patient including recent records",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient", type="object"),
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="total_encounters", type="integer", example=15),
     *                     @OA\Property(property="total_appointments", type="integer", example=8),
     *                     @OA\Property(property="total_prescriptions", type="integer", example=12),
     *                     @OA\Property(property="total_lab_results", type="integer", example=5),
     *                     @OA\Property(property="total_files", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/v1/patients/{patient}",
     *     summary="Update patient",
     *     description="Update patient information",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="dob", type="string", format="date", example="1990-01-15"),
     *             @OA\Property(property="sex", type="string", enum={"male","female","other"}, example="female"),
     *             @OA\Property(
     *                 property="contact",
     *                 type="object",
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                 @OA\Property(property="address", type="string", example="123 Main St, City, State")
     *             ),
     *             @OA\Property(property="allergies", type="array", @OA\Items(type="string"), example={"Penicillin", "Shellfish"}),
     *             @OA\Property(property="consents", type="array", @OA\Items(type="string"), example={"Treatment", "Data Sharing"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/v1/patients/{patient}",
     *     summary="Delete patient",
     *     description="Delete a patient record (only if no medical records exist)",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient deleted successfully"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete patient with existing medical records",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}/appointments",
     *     summary="Get patient appointments",
     *     description="Retrieve all appointments for a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *         name="status",
     *         in="query",
     *         description="Filter by appointment status",
     *         @OA\Schema(type="string", enum={"scheduled","confirmed","in_progress","completed","cancelled","no_show","rescheduled","waiting","checked_in","checked_out"})
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
     *     @OA\Response(
     *         response=200,
     *         description="Patient appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient appointments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(type="object")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}/encounters",
     *     summary="Get patient encounters",
     *     description="Retrieve all medical encounters for a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *         name="status",
     *         in="query",
     *         description="Filter by encounter status",
     *         @OA\Schema(type="string", enum={"scheduled","in_progress","completed","cancelled","no_show"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by encounter type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","routine_checkup","specialist_consultation","procedure","surgery","lab_test","imaging","physical_therapy"})
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
     *     @OA\Response(
     *         response=200,
     *         description="Patient encounters retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient encounters retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="patient_id", type="integer", example=1),
     *                                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
     *                                 @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="type", type="string", example="consultation"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="chief_complaint", type="string", example="Chest pain"),
     *                                 @OA\Property(property="doctor", type="object"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}/prescriptions",
     *     summary="Get patient prescriptions",
     *     description="Retrieve all prescriptions for a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *         name="status",
     *         in="query",
     *         description="Filter by prescription status",
     *         @OA\Schema(type="string", enum={"pending","verified","dispensed","cancelled","expired"})
     *     ),
     *     @OA\Parameter(
     *         name="prescription_type",
     *         in="query",
     *         description="Filter by prescription type",
     *         @OA\Schema(type="string", enum={"new","refill","emergency","controlled","compounded","over_the_counter","sample","discharge","maintenance"})
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
     *     @OA\Response(
     *         response=200,
     *         description="Patient prescriptions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient prescriptions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="patient_id", type="integer", example=1),
     *                                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
     *                                 @OA\Property(property="prescription_type", type="string", example="new"),
     *                                 @OA\Property(property="status", type="string", example="pending"),
     *                                 @OA\Property(property="issued_at", type="string", format="date-time"),
     *                                 @OA\Property(property="doctor", type="object"),
     *                                 @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}/lab-results",
     *     summary="Get patient lab results",
     *     description="Retrieve all laboratory test results for a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *         name="test_type",
     *         in="query",
     *         description="Filter by test type",
     *         @OA\Schema(type="string", enum={"blood","urine","imaging","biopsy","culture","other"}, example="blood")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by result status",
     *         @OA\Schema(type="string", enum={"pending","completed","abnormal","critical","cancelled"}, example="completed")
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
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by ordering doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="include_files",
     *         in="query",
     *         description="Include attached files",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient lab results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient lab results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="patient_id", type="integer", example=1),
     *                                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                                 @OA\Property(property="encounter_id", type="integer", example=1),
     *                                 @OA\Property(property="test_name", type="string", example="Complete Blood Count"),
     *                                 @OA\Property(property="test_type", type="string", example="blood"),
     *                                 @OA\Property(property="test_code", type="string", example="CBC"),
     *                                 @OA\Property(property="lab_name", type="string", example="Central Lab"),
     *                                 @OA\Property(property="ordered_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="collected_date", type="string", format="date", example="2024-01-15"),
     *                                 @OA\Property(property="result_date", type="string", format="date", example="2024-01-16"),
     *                                 @OA\Property(property="status", type="string", example="completed"),
     *                                 @OA\Property(property="results", type="object",
     *                                     @OA\Property(property="hemoglobin", type="object",
     *                                         @OA\Property(property="value", type="number", format="float", example=14.2),
     *                                         @OA\Property(property="unit", type="string", example="g/dL"),
     *                                         @OA\Property(property="reference_range", type="string", example="12.0-16.0"),
     *                                         @OA\Property(property="status", type="string", example="normal")
     *                                     ),
     *                                     @OA\Property(property="white_blood_cells", type="object",
     *                                         @OA\Property(property="value", type="number", format="float", example=7.5),
     *                                         @OA\Property(property="unit", type="string", example="K/uL"),
     *                                         @OA\Property(property="reference_range", type="string", example="4.5-11.0"),
     *                                         @OA\Property(property="status", type="string", example="normal")
     *                                     )
     *                                 ),
     *                                 @OA\Property(property="abnormal_values", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="critical_values", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="interpretation", type="string", example="All values within normal limits"),
     *                                 @OA\Property(property="recommendations", type="string", example="Continue current treatment"),
     *                                 @OA\Property(property="notes", type="string", example="Patient fasting for 12 hours"),
     *                                 @OA\Property(property="is_abnormal", type="boolean", example=false),
     *                                 @OA\Property(property="is_critical", type="boolean", example=false),
     *                                 @OA\Property(property="reviewed_by", type="string", example="Dr. Smith"),
     *                                 @OA\Property(property="reviewed_at", type="string", format="date-time"),
     *                                 @OA\Property(property="doctor", type="object", nullable=true),
     *                                 @OA\Property(property="encounter", type="object", nullable=true),
     *                                 @OA\Property(property="file_assets", type="array", @OA\Items(type="object")),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}/medical-history",
     *     summary="Get patient medical history",
     *     description="Retrieve comprehensive medical history for a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="include_family_history",
     *         in="query",
     *         description="Include family medical history",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_social_history",
     *         in="query",
     *         description="Include social history",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_medications",
     *         in="query",
     *         description="Include current and past medications",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_allergies",
     *         in="query",
     *         description="Include allergies and adverse reactions",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient medical history retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient medical history retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                     @OA\Property(property="gender", type="string", example="male")
     *                 ),
     *                 @OA\Property(property="medical_history", type="object"),
     *                 @OA\Property(property="family_history", type="object"),
     *                 @OA\Property(property="social_history", type="object"),
     *                 @OA\Property(property="current_medications", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="allergies", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="immunizations", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="summary", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/{patient}/file-assets",
     *     summary="Get patient files",
     *     description="Retrieve all file assets associated with a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *         name="category",
     *         in="query",
     *         description="Filter by file category",
     *         @OA\Schema(type="string", enum={"medical_images","lab_reports","prescriptions","insurance","identification","other"}, example="medical_images")
     *     ),
     *     @OA\Parameter(
     *         name="file_type",
     *         in="query",
     *         description="Filter by file type",
     *         @OA\Schema(type="string", enum={"image","document","pdf","video","audio","other"}, example="image")
     *     ),
     *     @OA\Parameter(
     *         name="is_private",
     *         in="query",
     *         description="Filter by privacy status",
     *         @OA\Schema(type="boolean", example=false)
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
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by filename or description",
     *         @OA\Schema(type="string", example="x-ray")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient files retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient files retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(type="object"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="patient_id", type="integer", example=1),
     *                                 @OA\Property(property="encounter_id", type="integer", example=1),
     *                                 @OA\Property(property="title", type="string", example="Chest X-Ray"),
     *                                 @OA\Property(property="description", type="string", example="Chest X-ray for chest pain evaluation"),
     *                                 @OA\Property(property="filename", type="string", example="chest_xray_20240115.jpg"),
     *                                 @OA\Property(property="original_filename", type="string", example="chest_xray.jpg"),
     *                                 @OA\Property(property="file_size", type="integer", example=2048576),
     *                                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                                 @OA\Property(property="category", type="string", example="medical_images"),
     *                                 @OA\Property(property="file_type", type="string", example="image"),
     *                                 @OA\Property(property="file_path", type="string", example="/storage/files/chest_xray_20240115.jpg"),
     *                                 @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                                 @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                                 @OA\Property(property="thumbnail_url", type="string", example="/api/v1/file-assets/1/thumbnail"),
     *                                 @OA\Property(property="is_private", type="boolean", example=false),
     *                                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"x-ray","chest","diagnostic"}),
     *                                 @OA\Property(property="uploaded_by", type="string", example="Dr. Smith"),
     *                                 @OA\Property(property="uploaded_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                                 @OA\Property(property="last_accessed", type="string", format="date-time", example="2024-01-16T14:30:00Z"),
     *                                 @OA\Property(property="access_count", type="integer", example=5),
     *                                 @OA\Property(property="encounter", type="object", nullable=true),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/patients/{patient}/file-assets",
     *     summary="Upload patient file",
     *     description="Upload a file asset for a specific patient",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="patient",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload"),
     *                 @OA\Property(property="title", type="string", example="Chest X-Ray Report", description="File title"),
     *                 @OA\Property(property="description", type="string", example="Chest X-ray for chest pain evaluation", description="File description"),
     *                 @OA\Property(property="category", type="string", example="medical_images", description="File category"),
     *                 @OA\Property(property="encounter_id", type="integer", example=1, description="Associated encounter ID"),
     *                 @OA\Property(property="is_private", type="boolean", example=false, description="Whether file is private"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"x-ray","chest","diagnostic"}, description="File tags")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="file_asset", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="encounter_id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Chest X-Ray Report"),
     *                     @OA\Property(property="description", type="string", example="Chest X-ray for chest pain evaluation"),
     *                     @OA\Property(property="filename", type="string", example="chest_xray_20240115.jpg"),
     *                     @OA\Property(property="original_filename", type="string", example="chest_xray.jpg"),
     *                     @OA\Property(property="file_size", type="integer", example=2048576),
     *                     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                     @OA\Property(property="category", type="string", example="medical_images"),
     *                     @OA\Property(property="file_path", type="string", example="/storage/files/chest_xray_20240115.jpg"),
     *                     @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                     @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                     @OA\Property(property="thumbnail_url", type="string", example="/api/v1/file-assets/1/thumbnail"),
     *                     @OA\Property(property="is_private", type="boolean", example=false),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="uploaded_by", type="string", example="Dr. Smith"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this patient",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/patients/search",
     *     summary="Search patients",
     *     description="Search patients by name, code, or other criteria",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="search_fields",
     *         in="query",
     *         description="Fields to search in",
     *         @OA\Schema(type="array", @OA\Items(type="string"), example={"name","code","phone","email"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search results retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="patients",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=5),
     *                 @OA\Property(property="query", type="string", example="John Doe")
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
     * @OA\Get(
     *     path="/api/v1/patients/recent",
     *     summary="Get recent patients",
     *     description="Retrieve recently accessed or updated patients",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of recent patients to retrieve",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of recent patients",
     *         @OA\Schema(type="string", enum={"accessed","updated","created","appointments"}, example="accessed")
     *     ),
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Number of days to look back",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recent patients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Recent patients retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="type", type="string", example="accessed"),
     *                 @OA\Property(property="days", type="integer", example=7),
     *                 @OA\Property(property="total_count", type="integer", example=15),
     *                 @OA\Property(
     *                     property="patients",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                         @OA\Property(property="phone", type="string", example="+1234567890"),
     *                         @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                         @OA\Property(property="gender", type="string", example="male"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="last_appointment", type="string", format="date", example="2024-01-10"),
     *                         @OA\Property(property="next_appointment", type="string", format="date", example="2024-02-10"),
     *                         @OA\Property(property="total_appointments", type="integer", example=15),
     *                         @OA\Property(property="last_accessed", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                         @OA\Property(property="last_updated", type="string", format="date-time", example="2024-01-15T09:30:00Z"),
     *                         @OA\Property(property="accessed_by", type="string", example="Dr. Smith"),
     *                         @OA\Property(property="access_count", type="integer", example=5),
     *                         @OA\Property(property="primary_doctor", type="object", nullable=true),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="summary", type="object",
     *                     @OA\Property(property="new_patients", type="integer", example=3),
     *                     @OA\Property(property="returning_patients", type="integer", example=12),
     *                     @OA\Property(property="most_accessed", type="string", example="John Doe"),
     *                     @OA\Property(property="average_access_count", type="number", format="float", example=3.2)
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
     * @OA\Get(
     *     path="/api/v1/patients/export",
     *     summary="Export patients",
     *     description="Export patient data in various formats",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Export format",
     *         @OA\Schema(type="string", enum={"csv","excel","pdf","json"}, example="csv")
     *     ),
     *     @OA\Parameter(
     *         name="fields",
     *         in="query",
     *         description="Fields to include in export",
     *         @OA\Schema(type="array", @OA\Items(type="string"), example={"id","first_name","last_name","email","phone","date_of_birth"})
     *     ),
     *     @OA\Parameter(
     *         name="filters",
     *         in="query",
     *         description="Filter criteria (JSON string)",
     *         @OA\Schema(type="string", example="status:active,date_from:2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="include_medical_history",
     *         in="query",
     *         description="Include medical history in export",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="include_appointments",
     *         in="query",
     *         description="Include appointment history",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="include_prescriptions",
     *         in="query",
     *         description="Include prescription history",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Export from date",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Export to date",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient export generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient export generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="export", type="object",
     *                     @OA\Property(property="id", type="string", example="EXP-2024-001"),
     *                     @OA\Property(property="format", type="string", example="csv"),
     *                     @OA\Property(property="filename", type="string", example="patients_export_20240115.csv"),
     *                     @OA\Property(property="file_size", type="integer", example=156789),
     *                     @OA\Property(property="total_records", type="integer", example=150),
     *                     @OA\Property(property="download_url", type="string", example="/api/v1/patients/export/EXP-2024-001/download"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-22T10:00:00Z"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="filters_applied", type="object",
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="date_from", type="string", format="date", example="2024-01-01"),
     *                         @OA\Property(property="date_to", type="string", format="date", example="2024-01-31")
     *                     ),
     *                     @OA\Property(property="fields_included", type="array", @OA\Items(type="string"), example={"id","first_name","last_name","email","phone","date_of_birth"}),
     *                     @OA\Property(property="includes", type="object",
     *                         @OA\Property(property="medical_history", type="boolean", example=false),
     *                         @OA\Property(property="appointments", type="boolean", example=false),
     *                         @OA\Property(property="prescriptions", type="boolean", example=false)
     *                     )
     *                 )
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
     * @OA\Get(
     *     path="/api/v1/patients/reports",
     *     summary="Get patient reports",
     *     description="Generate comprehensive reports for patient data and analytics",
     *     tags={"Patients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="report_type",
     *         in="query",
     *         description="Type of report to generate",
     *         @OA\Schema(type="string", enum={"summary","demographics","medical_history","appointments","prescriptions","lab_results","financial","compliance"}, example="summary")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Report period",
     *         @OA\Schema(type="string", enum={"today","week","month","quarter","year","custom"}, example="month")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for custom period",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for custom period",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="patient_status",
     *         in="query",
     *         description="Filter by patient status",
     *         @OA\Schema(type="string", enum={"active","inactive","deceased"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="age_range",
     *         in="query",
     *         description="Filter by age range",
     *         @OA\Schema(type="string", example="18-65")
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Report format",
     *         @OA\Schema(type="string", enum={"json","pdf","excel","csv"}, example="json")
     *     ),
     *     @OA\Parameter(
     *         name="include_charts",
     *         in="query",
     *         description="Include chart data in report",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient report generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Patient report generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="report", type="object",
     *                     @OA\Property(property="id", type="string", example="RPT-2024-001"),
     *                     @OA\Property(property="type", type="string", example="summary"),
     *                     @OA\Property(property="period", type="object",
     *                         @OA\Property(property="type", type="string", example="month"),
     *                         @OA\Property(property="from", type="string", format="date", example="2024-01-01"),
     *                         @OA\Property(property="to", type="string", format="date", example="2024-01-31")
     *                     ),
     *                     @OA\Property(property="generated_at", type="string", format="date-time", example="2024-01-31T23:59:59Z"),
     *                     @OA\Property(property="summary", type="object",
     *                         @OA\Property(property="total_patients", type="integer", example=150),
     *                         @OA\Property(property="new_patients", type="integer", example=25),
     *                         @OA\Property(property="active_patients", type="integer", example=142),
     *                         @OA\Property(property="inactive_patients", type="integer", example=8),
     *                         @OA\Property(property="total_appointments", type="integer", example=320),
     *                         @OA\Property(property="total_prescriptions", type="integer", example=180),
     *                         @OA\Property(property="total_lab_results", type="integer", example=95)
     *                     ),
     *                     @OA\Property(property="demographics", type="object",
     *                         @OA\Property(property="age_distribution", type="object",
     *                             @OA\Property(property="0-18", type="integer", example=15),
     *                             @OA\Property(property="19-35", type="integer", example=45),
     *                             @OA\Property(property="36-50", type="integer", example=35),
     *                             @OA\Property(property="51-65", type="integer", example=30),
     *                             @OA\Property(property="65+", type="integer", example=25)
     *                         ),
     *                         @OA\Property(property="gender_distribution", type="object",
     *                             @OA\Property(property="male", type="integer", example=75),
     *                             @OA\Property(property="female", type="integer", example=70),
     *                             @OA\Property(property="other", type="integer", example=5)
     *                         )
     *                     ),
     *                     @OA\Property(property="medical_conditions", type="array", @OA\Items(
     *                         @OA\Property(property="condition", type="string", example="Hypertension"),
     *                         @OA\Property(property="count", type="integer", example=45),
     *                         @OA\Property(property="percentage", type="number", format="float", example=30.0)
     *                     )),
     *                     @OA\Property(property="appointment_analytics", type="object",
     *                         @OA\Property(property="total_appointments", type="integer", example=320),
     *                         @OA\Property(property="completed_appointments", type="integer", example=295),
     *                         @OA\Property(property="cancelled_appointments", type="integer", example=15),
     *                         @OA\Property(property="no_show_appointments", type="integer", example=10),
     *                         @OA\Property(property="completion_rate", type="number", format="float", example=92.2)
     *                     ),
     *                     @OA\Property(property="prescription_analytics", type="object",
     *                         @OA\Property(property="total_prescriptions", type="integer", example=180),
     *                         @OA\Property(property="verified_prescriptions", type="integer", example=175),
     *                         @OA\Property(property="dispensed_prescriptions", type="integer", example=170),
     *                         @OA\Property(property="most_prescribed_medication", type="string", example="Lisinopril"),
     *                         @OA\Property(property="average_prescriptions_per_patient", type="number", format="float", example=1.2)
     *                     ),
     *                     @OA\Property(property="trends", type="object",
     *                         @OA\Property(property="patient_growth", type="string", example="increasing"),
     *                         @OA\Property(property="appointment_trend", type="string", example="stable"),
     *                         @OA\Property(property="prescription_trend", type="string", example="increasing")
     *                     ),
     *                     @OA\Property(property="recommendations", type="array", @OA\Items(type="string"), example={"Consider implementing patient reminder system","Monitor prescription compliance rates"}),
     *                     @OA\Property(property="charts", type="object",
     *                         @OA\Property(property="patient_growth", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="appointment_distribution", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="condition_prevalence", type="array", @OA\Items(type="object"))
     *                     ),
     *                     @OA\Property(property="export_urls", type="object",
     *                         @OA\Property(property="pdf", type="string", example="/api/v1/patients/reports/RPT-2024-001/download?format=pdf"),
     *                         @OA\Property(property="excel", type="string", example="/api/v1/patients/reports/RPT-2024-001/download?format=excel"),
     *                         @OA\Property(property="csv", type="string", example="/api/v1/patients/reports/RPT-2024-001/download?format=csv")
     *                     ),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-02-07T23:59:59Z")
     *                 )
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
