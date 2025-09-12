<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;




class ClinicController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/public/clinics",
     *     summary="Get public clinics",
     *     description="Retrieve a list of active clinics (public endpoint)",
     *     tags={"Clinics"},
     *     @OA\Response(
     *         response=200,
     *         description="Public clinics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Public clinics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="clinics",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="City Medical Center"),
     *                         @OA\Property(property="slug", type="string", example="city-medical-center"),
     *                         @OA\Property(property="address", type="string", example="123 Main St, City, State 12345"),
     *                         @OA\Property(property="phone", type="string", example="+1234567890"),
     *                         @OA\Property(property="email", type="string", format="email", example="info@clinic.com"),
     *                         @OA\Property(property="website", type="string", format="url", example="https://clinic.com"),
     *                         @OA\Property(property="description", type="string", example="Leading medical center")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $clinics = Clinic::where('is_active', true)
                ->select(['id', 'name', 'slug', 'address', 'phone', 'email', 'website', 'description'])
                ->get();

            return $this->successResponse([
                'clinics' => $clinics,
            ], 'Public clinics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/public/clinics/{clinic}",
     *     summary="Get public clinic details",
     *     description="Retrieve detailed information about a specific clinic (public endpoint)",
     *     tags={"Clinics"},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic details retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic", ref="#/components/schemas/Clinic")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function publicShow(Clinic $clinic): JsonResponse
    {
        try {
            if (!$clinic->is_active) {
                return $this->notFoundResponse('Clinic not found');
            }

            $clinic->load(['doctors.user']);

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic details retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics",
     *     summary="Get user's clinics (authenticated)",
     *     description="Retrieve a paginated list of clinics the authenticated user has access to",
     *     tags={"Clinics"},
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
     *         description="Search by clinic name",
     *         @OA\Schema(type="string", example="Medical")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","name","created_at","updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/Pagination"),
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/Clinic")
     *                         )
     *                     )
     *                 }
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
            $user = $this->getAuthenticatedUser();
            if (!$user instanceof User) {
                return $this->errorResponse('User not found', null, 404);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'name', 'created_at', 'updated_at'
            ]);

            $query = $user->clinics();

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%");
            }

            $clinics = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($clinics, 'Clinics retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clinics",
     *     summary="Create a new clinic",
     *     description="Create a new clinic and assign the current user as admin",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","slug","timezone"},
     *             @OA\Property(property="name", type="string", example="City Medical Center"),
     *             @OA\Property(property="slug", type="string", example="city-medical-center"),
     *             @OA\Property(property="timezone", type="string", example="America/New_York"),
     *             @OA\Property(property="logo_url", type="string", format="url", example="https://example.com/logo.png"),
     *             @OA\Property(
     *                 property="address",
     *                 type="object",
     *                 @OA\Property(property="street", type="string", example="123 Main St"),
     *                 @OA\Property(property="city", type="string", example="New York"),
     *                 @OA\Property(property="state", type="string", example="NY"),
     *                 @OA\Property(property="zip", type="string", example="10001"),
     *                 @OA\Property(property="country", type="string", example="USA")
     *             ),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="email", type="string", format="email", example="info@clinic.com"),
     *             @OA\Property(property="website", type="string", format="url", example="https://clinic.com"),
     *             @OA\Property(property="description", type="string", example="Leading medical center"),
     *             @OA\Property(property="settings", type="object", example={"appointment_duration": 30})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Clinic created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic", ref="#/components/schemas/Clinic")
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
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:clinics,slug',
                'timezone' => 'required|string|max:50',
                'logo_url' => 'nullable|url|max:500',
                'address' => 'nullable|array',
                'address.street' => 'nullable|string|max:255',
                'address.city' => 'nullable|string|max:100',
                'address.state' => 'nullable|string|max:100',
                'address.zip' => 'nullable|string|max:20',
                'address.country' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:1000',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $clinic = Clinic::create($validator->validated());

            // Assign current user to the clinic with admin role
            $user = $this->getAuthenticatedUser();
            $clinic->users()->attach($user->id, [
                'role_id' => 1, // Assuming 1 is admin role
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic created successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics/{clinic}",
     *     summary="Get clinic details",
     *     description="Retrieve detailed information about a specific clinic",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="include_users",
     *         in="query",
     *         description="Include clinic users",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="include_doctors",
     *         in="query",
     *         description="Include clinic doctors",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="include_statistics",
     *         in="query",
     *         description="Include clinic statistics",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic details retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="City Medical Center"),
     *                     @OA\Property(property="description", type="string", example="Full-service medical center providing comprehensive healthcare"),
     *                     @OA\Property(property="address", type="object",
     *                         @OA\Property(property="street", type="string", example="123 Main Street"),
     *                         @OA\Property(property="city", type="string", example="New York"),
     *                         @OA\Property(property="state", type="string", example="NY"),
     *                         @OA\Property(property="zip_code", type="string", example="10001"),
     *                         @OA\Property(property="country", type="string", example="USA")
     *                     ),
     *                     @OA\Property(property="contact", type="object",
     *                         @OA\Property(property="phone", type="string", example="+1-555-123-4567"),
     *                         @OA\Property(property="email", type="string", example="info@citymedical.com"),
     *                         @OA\Property(property="website", type="string", example="https://citymedical.com"),
     *                         @OA\Property(property="fax", type="string", example="+1-555-123-4568")
     *                     ),
     *                     @OA\Property(property="working_hours", type="object",
     *                         @OA\Property(property="monday", type="object",
     *                             @OA\Property(property="start", type="string", example="08:00"),
     *                             @OA\Property(property="end", type="string", example="18:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="tuesday", type="object",
     *                             @OA\Property(property="start", type="string", example="08:00"),
     *                             @OA\Property(property="end", type="string", example="18:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="wednesday", type="object",
     *                             @OA\Property(property="start", type="string", example="08:00"),
     *                             @OA\Property(property="end", type="string", example="18:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="thursday", type="object",
     *                             @OA\Property(property="start", type="string", example="08:00"),
     *                             @OA\Property(property="end", type="string", example="18:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="friday", type="object",
     *                             @OA\Property(property="start", type="string", example="08:00"),
     *                             @OA\Property(property="end", type="string", example="18:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="saturday", type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="15:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="sunday", type="object",
     *                             @OA\Property(property="start", type="string", example="00:00"),
     *                             @OA\Property(property="end", type="string", example="00:00"),
     *                             @OA\Property(property="is_open", type="boolean", example=false)
     *                         )
     *                     ),
     *                     @OA\Property(property="services", type="array", @OA\Items(type="string"), example={"General Medicine","Cardiology","Dermatology","Pediatrics"}),
     *                     @OA\Property(property="specialties", type="array", @OA\Items(type="string"), example={"Internal Medicine","Family Medicine","Emergency Medicine"}),
     *                     @OA\Property(property="facilities", type="array", @OA\Items(type="string"), example={"Laboratory","Radiology","Pharmacy","Emergency Room"}),
     *                     @OA\Property(property="insurance_accepted", type="array", @OA\Items(type="string"), example={"Blue Cross","Aetna","Cigna","Medicare"}),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="is_public", type="boolean", example=true),
     *                     @OA\Property(property="logo_url", type="string", example="/storage/clinics/logo_1.png"),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="string"), example={"/storage/clinics/image_1.jpg","/storage/clinics/image_2.jpg"}),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="users", type="array", @OA\Items(type="object"), nullable=true),
     *                 @OA\Property(property="doctors", type="array", @OA\Items(type="object"), nullable=true),
     *                 @OA\Property(property="statistics", type="object", nullable=true,
     *                     @OA\Property(property="total_users", type="integer", example=25),
     *                     @OA\Property(property="total_doctors", type="integer", example=8),
     *                     @OA\Property(property="total_patients", type="integer", example=150),
     *                     @OA\Property(property="total_appointments", type="integer", example=320),
     *                     @OA\Property(property="active_appointments", type="integer", example=45)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            $clinic->load(['users', 'doctors.user']);

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/clinics/{clinic}",
     *     summary="Update clinic",
     *     description="Update clinic information and settings",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="City Medical Center", description="Clinic name"),
     *             @OA\Property(property="description", type="string", example="Full-service medical center providing comprehensive healthcare", description="Clinic description"),
     *             @OA\Property(property="address", type="object",
     *                 @OA\Property(property="street", type="string", example="123 Main Street"),
     *                 @OA\Property(property="city", type="string", example="New York"),
     *                 @OA\Property(property="state", type="string", example="NY"),
     *                 @OA\Property(property="zip_code", type="string", example="10001"),
     *                 @OA\Property(property="country", type="string", example="USA")
     *             ),
     *             @OA\Property(property="contact", type="object",
     *                 @OA\Property(property="phone", type="string", example="+1-555-123-4567"),
     *                 @OA\Property(property="email", type="string", example="info@citymedical.com"),
     *                 @OA\Property(property="website", type="string", example="https://citymedical.com"),
     *                 @OA\Property(property="fax", type="string", example="+1-555-123-4568")
     *             ),
     *             @OA\Property(property="working_hours", type="object",
     *                 @OA\Property(property="monday", type="object",
     *                     @OA\Property(property="start", type="string", example="08:00"),
     *                     @OA\Property(property="end", type="string", example="18:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="tuesday", type="object",
     *                     @OA\Property(property="start", type="string", example="08:00"),
     *                     @OA\Property(property="end", type="string", example="18:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="wednesday", type="object",
     *                     @OA\Property(property="start", type="string", example="08:00"),
     *                     @OA\Property(property="end", type="string", example="18:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="thursday", type="object",
     *                     @OA\Property(property="start", type="string", example="08:00"),
     *                     @OA\Property(property="end", type="string", example="18:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="friday", type="object",
     *                     @OA\Property(property="start", type="string", example="08:00"),
     *                     @OA\Property(property="end", type="string", example="18:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="saturday", type="object",
     *                     @OA\Property(property="start", type="string", example="09:00"),
     *                     @OA\Property(property="end", type="string", example="15:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=true)
     *                 ),
     *                 @OA\Property(property="sunday", type="object",
     *                     @OA\Property(property="start", type="string", example="00:00"),
     *                     @OA\Property(property="end", type="string", example="00:00"),
     *                     @OA\Property(property="is_open", type="boolean", example=false)
     *                 )
     *             ),
     *             @OA\Property(property="services", type="array", @OA\Items(type="string"), example={"General Medicine","Cardiology","Dermatology","Pediatrics"}, description="Services offered"),
     *             @OA\Property(property="specialties", type="array", @OA\Items(type="string"), example={"Internal Medicine","Family Medicine","Emergency Medicine"}, description="Medical specialties"),
     *             @OA\Property(property="facilities", type="array", @OA\Items(type="string"), example={"Laboratory","Radiology","Pharmacy","Emergency Room"}, description="Available facilities"),
     *             @OA\Property(property="insurance_accepted", type="array", @OA\Items(type="string"), example={"Blue Cross","Aetna","Cigna","Medicare"}, description="Accepted insurance providers"),
     *             @OA\Property(property="is_active", type="boolean", example=true, description="Whether clinic is active"),
     *             @OA\Property(property="is_public", type="boolean", example=true, description="Whether clinic is publicly visible"),
     *             @OA\Property(property="settings", type="object",
     *                 @OA\Property(property="appointment_duration", type="integer", example=30, description="Default appointment duration in minutes"),
     *                 @OA\Property(property="buffer_time", type="integer", example=5, description="Buffer time between appointments"),
     *                 @OA\Property(property="max_appointments_per_day", type="integer", example=50, description="Maximum appointments per day"),
     *                 @OA\Property(property="allow_online_booking", type="boolean", example=true, description="Allow online appointment booking"),
     *                 @OA\Property(property="require_patient_verification", type="boolean", example=true, description="Require patient verification for appointments")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="City Medical Center"),
     *                     @OA\Property(property="description", type="string", example="Full-service medical center providing comprehensive healthcare"),
     *                     @OA\Property(property="address", type="object"),
     *                     @OA\Property(property="contact", type="object"),
     *                     @OA\Property(property="working_hours", type="object"),
     *                     @OA\Property(property="services", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="specialties", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="facilities", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="insurance_accepted", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="is_public", type="boolean", example=true),
     *                     @OA\Property(property="settings", type="object"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|required|string|max:255|unique:clinics,slug,' . $clinic->id,
                'timezone' => 'sometimes|required|string|max:50',
                'logo_url' => 'nullable|url|max:500',
                'address' => 'nullable|array',
                'address.street' => 'nullable|string|max:255',
                'address.city' => 'nullable|string|max:100',
                'address.state' => 'nullable|string|max:100',
                'address.zip' => 'nullable|string|max:20',
                'address.country' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:1000',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $clinic->update($validator->validated());

            return $this->successResponse([
                'clinic' => $clinic,
            ], 'Clinic updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clinics/{clinic}",
     *     summary="Delete clinic",
     *     description="Delete a clinic and all associated data",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="Force delete (permanently remove all data)",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="transfer_data_to",
     *         in="query",
     *         description="Transfer data to another clinic ID",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic deleted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="City Medical Center"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *                 ),
     *                 @OA\Property(property="deletion_summary", type="object",
     *                     @OA\Property(property="users_affected", type="integer", example=25),
     *                     @OA\Property(property="doctors_affected", type="integer", example=8),
     *                     @OA\Property(property="patients_affected", type="integer", example=150),
     *                     @OA\Property(property="appointments_affected", type="integer", example=320),
     *                     @OA\Property(property="prescriptions_affected", type="integer", example=180),
     *                     @OA\Property(property="lab_results_affected", type="integer", example=95),
     *                     @OA\Property(property="file_assets_affected", type="integer", example=45)
     *                 ),
     *                 @OA\Property(property="data_transfer", type="object", nullable=true,
     *                     @OA\Property(property="transferred_to_clinic", type="integer", example=2),
     *                     @OA\Property(property="transferred_to_name", type="string", example="Downtown Medical Center"),
     *                     @OA\Property(property="transfer_status", type="string", example="completed"),
     *                     @OA\Property(property="transfer_summary", type="object",
     *                         @OA\Property(property="users_transferred", type="integer", example=25),
     *                         @OA\Property(property="patients_transferred", type="integer", example=150),
     *                         @OA\Property(property="appointments_transferred", type="integer", example=320)
     *                     )
     *                 ),
     *                 @OA\Property(property="backup_created", type="boolean", example=true),
     *                 @OA\Property(property="backup_location", type="string", example="/storage/backups/clinic_1_backup_20240115.zip"),
     *                 @OA\Property(property="retention_period", type="string", example="30 days"),
     *                 @OA\Property(property="can_be_restored", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this clinic or insufficient permissions",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete clinic with active data or validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            // Check if clinic has any data
            $hasData = $clinic->patients()->exists() ||
                      $clinic->appointments()->exists() ||
                      $clinic->encounters()->exists();

            if ($hasData) {
                return $this->errorResponse('Cannot delete clinic with existing data', null, 422);
            }

            $clinic->delete();

            return $this->successResponse(null, 'Clinic deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics/{clinic}/users",
     *     summary="Get clinic users",
     *     description="Retrieve all users associated with a specific clinic",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
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
     *         name="role",
     *         in="query",
     *         description="Filter by user role",
     *         @OA\Schema(type="string", enum={"admin","doctor","nurse","receptionist","patient"}, example="doctor")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by user status",
     *         @OA\Schema(type="string", enum={"active","inactive","suspended"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="include_permissions",
     *         in="query",
     *         description="Include user permissions",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic users retrieved successfully"),
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
     *                                 @OA\Property(property="first_name", type="string", example="John"),
     *                                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                                 @OA\Property(property="role", type="string", example="doctor"),
     *                                 @OA\Property(property="status", type="string", example="active"),
     *                                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                                 @OA\Property(property="last_login", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                                 @OA\Property(property="clinic_role", type="object",
     *                                     @OA\Property(property="role", type="string", example="doctor"),
     *                                     @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"view_patients","create_appointments","manage_prescriptions"}),
     *                                     @OA\Property(property="assigned_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
     *                                     @OA\Property(property="assigned_by", type="string", example="Admin User")
     *                                 ),
     *                                 @OA\Property(property="profile", type="object", nullable=true,
     *                                     @OA\Property(property="avatar", type="string", example="/storage/avatars/user_1.jpg"),
     *                                     @OA\Property(property="bio", type="string", example="Experienced cardiologist with 10+ years of practice"),
     *                                     @OA\Property(property="specialization", type="string", example="Cardiology"),
     *                                     @OA\Property(property="license_number", type="string", example="MD123456")
     *                                 ),
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
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function users(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $users = $clinic->users()
                ->with(['roles'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($users, 'Clinic users retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics/{clinic}/doctors",
     *     summary="Get clinic doctors",
     *     description="Retrieve all doctors associated with a specific clinic",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
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
     *         name="specialization",
     *         in="query",
     *         description="Filter by specialization",
     *         @OA\Schema(type="string", example="Cardiology")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by doctor status",
     *         @OA\Schema(type="string", enum={"active","inactive","on_leave"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or specialization",
     *         @OA\Schema(type="string", example="Dr. Smith")
     *     ),
     *     @OA\Parameter(
     *         name="include_availability",
     *         in="query",
     *         description="Include doctor availability",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic doctors retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic doctors retrieved successfully"),
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
     *                                 @OA\Property(property="user_id", type="integer", example=1),
     *                                 @OA\Property(property="first_name", type="string", example="John"),
     *                                 @OA\Property(property="last_name", type="string", example="Smith"),
     *                                 @OA\Property(property="email", type="string", example="dr.smith@example.com"),
     *                                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                                 @OA\Property(property="specialization", type="string", example="Cardiology"),
     *                                 @OA\Property(property="license_number", type="string", example="MD123456"),
     *                                 @OA\Property(property="status", type="string", example="active"),
     *                                 @OA\Property(property="experience_years", type="integer", example=10),
     *                                 @OA\Property(property="education", type="array", @OA\Items(type="string"), example={"MD - Harvard Medical School","Residency - Johns Hopkins"}),
     *                                 @OA\Property(property="certifications", type="array", @OA\Items(type="string"), example={"Board Certified in Cardiology","ACLS Certified"}),
     *                                 @OA\Property(property="languages", type="array", @OA\Items(type="string"), example={"English","Spanish","French"}),
     *                                 @OA\Property(property="consultation_fee", type="number", format="float", example=150.00),
     *                                 @OA\Property(property="follow_up_fee", type="number", format="float", example=100.00),
     *                                 @OA\Property(property="bio", type="string", example="Experienced cardiologist with 10+ years of practice"),
     *                                 @OA\Property(property="avatar", type="string", example="/storage/avatars/doctor_1.jpg"),
     *                                 @OA\Property(property="rating", type="number", format="float", example=4.8),
     *                                 @OA\Property(property="total_reviews", type="integer", example=125),
     *                                 @OA\Property(property="total_patients", type="integer", example=45),
     *                                 @OA\Property(property="total_appointments", type="integer", example=320),
     *                                 @OA\Property(property="next_available_slot", type="string", format="date-time", example="2024-01-16T10:00:00Z"),
     *                                 @OA\Property(property="availability", type="object", nullable=true),
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
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function doctors(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $doctors = $clinic->doctors()
                ->with(['user'])
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($doctors, 'Clinic doctors retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics/{clinic}/patients",
     *     summary="Get clinic patients",
     *     description="Retrieve all patients associated with a specific clinic",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
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
     *         description="Filter by patient status",
     *         @OA\Schema(type="string", enum={"active","inactive","deceased"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or ID",
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="age_range",
     *         in="query",
     *         description="Filter by age range",
     *         @OA\Schema(type="string", example="18-65")
     *     ),
     *     @OA\Parameter(
     *         name="doctor_id",
     *         in="query",
     *         description="Filter by primary doctor",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="last_visit_from",
     *         in="query",
     *         description="Filter by last visit date from",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="last_visit_to",
     *         in="query",
     *         description="Filter by last visit date to",
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic patients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic patients retrieved successfully"),
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
     *                                 @OA\Property(property="first_name", type="string", example="John"),
     *                                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                                 @OA\Property(property="gender", type="string", example="male"),
     *                                 @OA\Property(property="status", type="string", example="active"),
     *                                 @OA\Property(property="emergency_contact", type="object",
     *                                     @OA\Property(property="name", type="string", example="Jane Doe"),
     *                                     @OA\Property(property="phone", type="string", example="+1234567891"),
     *                                     @OA\Property(property="relationship", type="string", example="spouse")
     *                                 ),
     *                                 @OA\Property(property="medical_history", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="allergies", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="current_medications", type="array", @OA\Items(type="string")),
     *                                 @OA\Property(property="last_appointment", type="string", format="date", example="2024-01-10"),
     *                                 @OA\Property(property="next_appointment", type="string", format="date", example="2024-02-10"),
     *                                 @OA\Property(property="total_appointments", type="integer", example=15),
     *                                 @OA\Property(property="total_encounters", type="integer", example=12),
     *                                 @OA\Property(property="total_prescriptions", type="integer", example=8),
     *                                 @OA\Property(property="primary_doctor", type="object", nullable=true,
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="Dr. Smith"),
     *                                     @OA\Property(property="specialization", type="string", example="Cardiology")
     *                                 ),
     *                                 @OA\Property(property="insurance", type="object", nullable=true,
     *                                     @OA\Property(property="provider", type="string", example="Blue Cross"),
     *                                     @OA\Property(property="policy_number", type="string", example="BC123456789"),
     *                                     @OA\Property(property="group_number", type="string", example="GRP001")
     *                                 ),
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
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function patients(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $patients = $clinic->patients()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($patients, 'Clinic patients retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics/{clinic}/appointments",
     *     summary="Get clinic appointments",
     *     description="Retrieve all appointments for a specific clinic",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
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
     *         @OA\Schema(type="string", enum={"scheduled","confirmed","in_progress","completed","cancelled","no_show"}, example="scheduled")
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
     *         name="appointment_type",
     *         in="query",
     *         description="Filter by appointment type",
     *         @OA\Schema(type="string", enum={"consultation","follow_up","emergency","procedure","telemedicine"}, example="consultation")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic appointments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic appointments retrieved successfully"),
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
     *                                 @OA\Property(property="appointment_date", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                                 @OA\Property(property="duration_minutes", type="integer", example=30),
     *                                 @OA\Property(property="status", type="string", example="scheduled"),
     *                                 @OA\Property(property="appointment_type", type="string", example="consultation"),
     *                                 @OA\Property(property="reason", type="string", example="Regular checkup"),
     *                                 @OA\Property(property="notes", type="string", example="Patient requested morning appointment"),
     *                                 @OA\Property(property="check_in_time", type="string", format="date-time", nullable=true),
     *                                 @OA\Property(property="check_out_time", type="string", format="date-time", nullable=true),
     *                                 @OA\Property(property="room_number", type="string", example="Room 101"),
     *                                 @OA\Property(property="is_urgent", type="boolean", example=false),
     *                                 @OA\Property(property="is_follow_up", type="boolean", example=false),
     *                                 @OA\Property(property="reminder_sent", type="boolean", example=true),
     *                                 @OA\Property(property="reminder_sent_at", type="string", format="date-time", example="2024-01-14T18:00:00Z"),
     *                                 @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true),
     *                                 @OA\Property(property="cancellation_reason", type="string", nullable=true),
     *                                 @OA\Property(property="patient", type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="first_name", type="string", example="John"),
     *                                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                                     @OA\Property(property="email", type="string", example="john.doe@example.com")
     *                                 ),
     *                                 @OA\Property(property="doctor", type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="first_name", type="string", example="Dr. Smith"),
     *                                     @OA\Property(property="last_name", type="string", example="Smith"),
     *                                     @OA\Property(property="specialization", type="string", example="Cardiology")
     *                                 ),
     *                                 @OA\Property(property="clinic", type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="City Medical Center"),
     *                                     @OA\Property(property="address", type="object")
     *                                 ),
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
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function appointments(Request $request, Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            [$perPage, $page] = $this->getPaginationParams($request);

            $appointments = $clinic->appointments()
                ->with(['patient', 'doctor.user', 'room'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($appointments, 'Clinic appointments retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clinics/{clinic}/statistics",
     *     summary="Get clinic statistics",
     *     description="Retrieve comprehensive statistics and analytics for a specific clinic",
     *     tags={"Clinics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="clinic",
     *         in="path",
     *         description="Clinic ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Statistics period",
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
     *         name="include_trends",
     *         in="query",
     *         description="Include trend analysis",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="include_comparisons",
     *         in="query",
     *         description="Include period comparisons",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clinic statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clinic statistics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clinic", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="City Medical Center"),
     *                     @OA\Property(property="period", type="object",
     *                         @OA\Property(property="type", type="string", example="month"),
     *                         @OA\Property(property="from", type="string", format="date", example="2024-01-01"),
     *                         @OA\Property(property="to", type="string", format="date", example="2024-01-31")
     *                     )
     *                 ),
     *                 @OA\Property(property="overview", type="object",
     *                     @OA\Property(property="total_users", type="integer", example=25),
     *                     @OA\Property(property="total_doctors", type="integer", example=8),
     *                     @OA\Property(property="total_patients", type="integer", example=150),
     *                     @OA\Property(property="total_appointments", type="integer", example=320),
     *                     @OA\Property(property="total_encounters", type="integer", example=280),
     *                     @OA\Property(property="total_prescriptions", type="integer", example=180),
     *                     @OA\Property(property="total_lab_results", type="integer", example=95),
     *                     @OA\Property(property="total_file_assets", type="integer", example=45)
     *                 ),
     *                 @OA\Property(property="appointment_statistics", type="object",
     *                     @OA\Property(property="total_appointments", type="integer", example=320),
     *                     @OA\Property(property="completed_appointments", type="integer", example=295),
     *                     @OA\Property(property="cancelled_appointments", type="integer", example=15),
     *                     @OA\Property(property="no_show_appointments", type="integer", example=10),
     *                     @OA\Property(property="completion_rate", type="number", format="float", example=92.2),
     *                     @OA\Property(property="cancellation_rate", type="number", format="float", example=4.7),
     *                     @OA\Property(property="no_show_rate", type="number", format="float", example=3.1),
     *                     @OA\Property(property="average_appointment_duration", type="number", format="float", example=28.5),
     *                     @OA\Property(property="peak_hours", type="array", @OA\Items(type="string"), example={"09:00-11:00","14:00-16:00"}),
     *                     @OA\Property(property="busiest_days", type="array", @OA\Items(type="string"), example={"Monday","Wednesday","Friday"})
     *                 ),
     *                 @OA\Property(property="patient_statistics", type="object",
     *                     @OA\Property(property="new_patients", type="integer", example=25),
     *                     @OA\Property(property="returning_patients", type="integer", example=125),
     *                     @OA\Property(property="active_patients", type="integer", example=142),
     *                     @OA\Property(property="inactive_patients", type="integer", example=8),
     *                     @OA\Property(property="patient_retention_rate", type="number", format="float", example=85.3),
     *                     @OA\Property(property="average_visits_per_patient", type="number", format="float", example=2.1),
     *                     @OA\Property(property="age_distribution", type="object",
     *                         @OA\Property(property="0-18", type="integer", example=15),
     *                         @OA\Property(property="19-35", type="integer", example=45),
     *                         @OA\Property(property="36-50", type="integer", example=35),
     *                         @OA\Property(property="51-65", type="integer", example=30),
     *                         @OA\Property(property="65+", type="integer", example=25)
     *                     ),
     *                     @OA\Property(property="gender_distribution", type="object",
     *                         @OA\Property(property="male", type="integer", example=75),
     *                         @OA\Property(property="female", type="integer", example=70),
     *                         @OA\Property(property="other", type="integer", example=5)
     *                     )
     *                 ),
     *                 @OA\Property(property="doctor_statistics", type="object",
     *                     @OA\Property(property="total_doctors", type="integer", example=8),
     *                     @OA\Property(property="active_doctors", type="integer", example=7),
     *                     @OA\Property(property="on_leave_doctors", type="integer", example=1),
     *                     @OA\Property(property="average_patients_per_doctor", type="number", format="float", example=18.8),
     *                     @OA\Property(property="average_appointments_per_doctor", type="number", format="float", example=40.0),
     *                     @OA\Property(property="specialization_distribution", type="object",
     *                         @OA\Property(property="Cardiology", type="integer", example=2),
     *                         @OA\Property(property="Dermatology", type="integer", example=1),
     *                         @OA\Property(property="General Medicine", type="integer", example=3),
     *                         @OA\Property(property="Pediatrics", type="integer", example=2)
     *                     )
     *                 ),
     *                 @OA\Property(property="prescription_statistics", type="object",
     *                     @OA\Property(property="total_prescriptions", type="integer", example=180),
     *                     @OA\Property(property="verified_prescriptions", type="integer", example=175),
     *                     @OA\Property(property="dispensed_prescriptions", type="integer", example=170),
     *                     @OA\Property(property="pending_prescriptions", type="integer", example=5),
     *                     @OA\Property(property="verification_rate", type="number", format="float", example=97.2),
     *                     @OA\Property(property="dispensing_rate", type="number", format="float", example=94.4),
     *                     @OA\Property(property="most_prescribed_medications", type="array", @OA\Items(
     *                         @OA\Property(property="medication", type="string", example="Lisinopril"),
     *                         @OA\Property(property="count", type="integer", example=25),
     *                         @OA\Property(property="percentage", type="number", format="float", example=13.9)
     *                     ))
     *                 ),
     *                 @OA\Property(property="lab_statistics", type="object",
     *                     @OA\Property(property="total_lab_results", type="integer", example=95),
     *                     @OA\Property(property="completed_results", type="integer", example=90),
     *                     @OA\Property(property="pending_results", type="integer", example=5),
     *                     @OA\Property(property="abnormal_results", type="integer", example=12),
     *                     @OA\Property(property="critical_results", type="integer", example=2),
     *                     @OA\Property(property="completion_rate", type="number", format="float", example=94.7),
     *                     @OA\Property(property="abnormal_rate", type="number", format="float", example=12.6),
     *                     @OA\Property(property="most_common_tests", type="array", @OA\Items(
     *                         @OA\Property(property="test", type="string", example="Complete Blood Count"),
     *                         @OA\Property(property="count", type="integer", example=20),
     *                         @OA\Property(property="percentage", type="number", format="float", example=21.1)
     *                     ))
     *                 ),
     *                 @OA\Property(property="financial_statistics", type="object",
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=45000.00),
     *                     @OA\Property(property="average_revenue_per_appointment", type="number", format="float", example=140.63),
     *                     @OA\Property(property="insurance_claims", type="integer", example=280),
     *                     @OA\Property(property="pending_claims", type="integer", example=15),
     *                     @OA\Property(property="approved_claims", type="integer", example=250),
     *                     @OA\Property(property="rejected_claims", type="integer", example=15),
     *                     @OA\Property(property="claim_approval_rate", type="number", format="float", example=94.3)
     *                 ),
     *                 @OA\Property(property="trends", type="object",
     *                     @OA\Property(property="patient_growth", type="string", example="increasing"),
     *                     @OA\Property(property="appointment_trend", type="string", example="stable"),
     *                     @OA\Property(property="revenue_trend", type="string", example="increasing"),
     *                     @OA\Property(property="prescription_trend", type="string", example="increasing"),
     *                     @OA\Property(property="lab_result_trend", type="string", example="stable")
     *                 ),
     *                 @OA\Property(property="comparisons", type="object",
     *                     @OA\Property(property="previous_period", type="object",
     *                         @OA\Property(property="total_appointments", type="integer", example=310),
     *                         @OA\Property(property="total_patients", type="integer", example=145),
     *                         @OA\Property(property="total_revenue", type="number", format="float", example=42000.00)
     *                     ),
     *                     @OA\Property(property="growth_rates", type="object",
     *                         @OA\Property(property="appointment_growth", type="number", format="float", example=3.2),
     *                         @OA\Property(property="patient_growth", type="number", format="float", example=3.4),
     *                         @OA\Property(property="revenue_growth", type="number", format="float", example=7.1)
     *                     )
     *                 ),
     *                 @OA\Property(property="generated_at", type="string", format="date-time", example="2024-01-31T23:59:59Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this clinic",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Clinic not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function statistics(Clinic $clinic): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($clinic->id)) {
                return $this->forbiddenResponse('No access to this clinic');
            }

            $statistics = [
                'total_users' => $clinic->users()->count(),
                'total_doctors' => $clinic->doctors()->count(),
                'total_patients' => $clinic->patients()->count(),
                'total_appointments' => $clinic->appointments()->count(),
                'total_encounters' => $clinic->encounters()->count(),
                'total_prescriptions' => $clinic->prescriptions()->count(),
                'total_lab_results' => $clinic->labResults()->count(),

                'appointments_today' => $clinic->appointments()
                    ->whereDate('start_at', now()->toDateString())
                    ->count(),
                'appointments_this_week' => $clinic->appointments()
                    ->whereBetween('start_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'appointments_this_month' => $clinic->appointments()
                    ->whereBetween('start_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),

                'new_patients_this_month' => $clinic->patients()
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count(),

                'appointments_by_status' => $clinic->appointments()
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),

                'appointments_by_type' => $clinic->appointments()
                    ->selectRaw('appointment_type, COUNT(*) as count')
                    ->groupBy('appointment_type')
                    ->get(),
            ];

            return $this->successResponse([
                'statistics' => $statistics,
            ], 'Clinic statistics retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
