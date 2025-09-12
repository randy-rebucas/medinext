<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     title="MediNext EMR API",
 *     version="1.0.0",
 *     description="Comprehensive API for MediNext Electronic Medical Records system",
 *     @OA\Contact(
 *         name="MediNext Support",
 *         email="support@medinext.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="MediNext API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Enter token in format (Bearer <token>)"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization"
 * )
 *
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard and analytics endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Clinics",
 *     description="Clinic management operations"
 * )
 *
 * @OA\Tag(
 *     name="Patients",
 *     description="Patient management operations"
 * )
 *
 * @OA\Tag(
 *     name="Doctors",
 *     description="Doctor management operations"
 * )
 *
 * @OA\Tag(
 *     name="Appointments",
 *     description="Appointment scheduling and management"
 * )
 *
 * @OA\Tag(
 *     name="Encounters",
 *     description="Medical encounter management"
 * )
 *
 * @OA\Tag(
 *     name="Prescriptions",
 *     description="Prescription management and tracking"
 * )
 *
 * @OA\Tag(
 *     name="Lab Results",
 *     description="Laboratory results management"
 * )
 *
 * @OA\Tag(
 *     name="File Assets",
 *     description="File upload and management"
 * )
 *
 * @OA\Tag(
 *     name="Medreps",
 *     description="Medical representative management"
 * )
 *
 * @OA\Tag(
 *     name="Settings",
 *     description="System and user settings"
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="message", type="string", description="Error message"),
 *     @OA\Property(property="errors", type="object", description="Validation errors")
 * )
 *
 * @OA\Schema(
 *     schema="Success",
 *     type="object",
 *     @OA\Property(property="message", type="string", description="Success message"),
 *     @OA\Property(property="data", type="object", description="Response data")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Dr. John Smith"),
 *     @OA\Property(property="email", type="string", format="email", example="doctor@clinic.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Patient",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="PTCLN20241201001"),
 *     @OA\Property(property="first_name", type="string", example="Jane"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="dob", type="string", format="date", example="1990-01-15"),
 *     @OA\Property(property="sex", type="string", enum={"male","female","other"}, example="female"),
 *     @OA\Property(property="contact", type="object", nullable=true),
 *     @OA\Property(property="allergies", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="consents", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Doctor",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="specialization", type="string", example="Cardiology"),
 *     @OA\Property(property="license_number", type="string", example="MD123456"),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Appointment",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="doctor_id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="room_id", type="integer", nullable=true),
 *     @OA\Property(property="start_at", type="string", format="date-time"),
 *     @OA\Property(property="end_at", type="string", format="date-time"),
 *     @OA\Property(property="duration", type="integer", example=30, description="Duration in minutes"),
 *     @OA\Property(property="appointment_type", type="string", enum={"consultation","follow_up","emergency","routine_checkup","specialist_consultation","procedure","surgery","lab_test","imaging","physical_therapy"}),
 *     @OA\Property(property="status", type="string", enum={"scheduled","confirmed","in_progress","completed","cancelled","no_show","rescheduled","waiting","checked_in","checked_out"}),
 *     @OA\Property(property="reason", type="string", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="priority", type="string", enum={"low","normal","high","urgent","emergency"}),
 *     @OA\Property(property="patient", ref="#/components/schemas/Patient"),
 *     @OA\Property(property="doctor", ref="#/components/schemas/Doctor"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Clinic",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="City Medical Center"),
 *     @OA\Property(property="slug", type="string", example="city-medical-center"),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, State 12345"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="email", type="string", format="email", example="info@clinic.com"),
 *     @OA\Property(property="website", type="string", format="url", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="first_page_url", type="string"),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="last_page_url", type="string"),
 *     @OA\Property(property="links", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="next_page_url", type="string", nullable=true),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=150)
 * )
 */
class BaseController extends Controller
{
    /**
     * Return a success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error response
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse($errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Get the authenticated user
     */
    protected function getAuthenticatedUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return Auth::guard('sanctum')->user();
    }

    /**
     * Get the current clinic for the authenticated user
     */
    protected function getCurrentClinic(): ?\App\Models\Clinic
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return null;
        }

        // Get the first clinic the user has access to
        /** @var \App\Models\User $user */
        $userClinicRole = $user->userClinicRoles()->with('clinic')->first();
        return $userClinicRole ? $userClinicRole->clinic : null;
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);

        // Limit per_page to prevent excessive queries
        $perPage = min($perPage, 100);

        return [$perPage, $page];
    }

    /**
     * Get sorting parameters from request
     */
    protected function getSortingParams(Request $request, array $allowedFields = []): array
    {
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'asc');

        // Validate sort field
        if (!empty($allowedFields) && !in_array($sort, $allowedFields)) {
            $sort = 'id';
        }

        // Validate direction
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }

        return [$sort, $direction];
    }

    /**
     * Return a paginated response
     */
    protected function paginatedResponse($paginatedData, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'last_page' => $paginatedData->lastPage(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
                'has_more_pages' => $paginatedData->hasMorePages(),
            ]
        ]);
    }

    /**
     * Check if user has access to a specific clinic
     */
    protected function hasClinicAccess(int $clinicId): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->userClinicRoles()->where('clinic_id', $clinicId)->exists();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->hasPermission($permission);
    }

    /**
     * Check if user has any of the specified permissions
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the specified permissions
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Check if user has a specific role
     */
    protected function hasRole(string $role): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->hasRole($role);
    }

    /**
     * Check if user has a specific role in a clinic
     */
    protected function hasRoleInClinic(string $role, int $clinicId): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->hasRoleInClinic($role, $clinicId);
    }

    /**
     * Check if user has permission in a specific clinic
     */
    protected function hasPermissionInClinic(string $permission, int $clinicId): bool
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->hasPermissionInClinic($permission, $clinicId);
    }

    /**
     * Ensure user has a specific permission, throw exception if not
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            throw new \Illuminate\Auth\Access\AuthorizationException("Insufficient permissions. Required: {$permission}");
        }
    }

    /**
     * Ensure user has any of the specified permissions, throw exception if not
     */
    protected function requireAnyPermission(array $permissions): void
    {
        if (!$this->hasAnyPermission($permissions)) {
            $permissionsList = implode(', ', $permissions);
            throw new \Illuminate\Auth\Access\AuthorizationException("Insufficient permissions. Required one of: {$permissionsList}");
        }
    }

    /**
     * Ensure user has all of the specified permissions, throw exception if not
     */
    protected function requireAllPermissions(array $permissions): void
    {
        if (!$this->hasAllPermissions($permissions)) {
            $permissionsList = implode(', ', $permissions);
            throw new \Illuminate\Auth\Access\AuthorizationException("Insufficient permissions. Required all of: {$permissionsList}");
        }
    }

    /**
     * Ensure user has access to a specific clinic, throw exception if not
     */
    protected function requireClinicAccess(int $clinicId): void
    {
        if (!$this->hasClinicAccess($clinicId)) {
            throw new \Illuminate\Auth\Access\AuthorizationException("No access to clinic ID: {$clinicId}");
        }
    }

    /**
     * Ensure user has a specific role, throw exception if not
     */
    protected function requireRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            throw new \Illuminate\Auth\Access\AuthorizationException("Insufficient role. Required: {$role}");
        }
    }

    /**
     * Ensure user has a specific role in a clinic, throw exception if not
     */
    protected function requireRoleInClinic(string $role, int $clinicId): void
    {
        if (!$this->hasRoleInClinic($role, $clinicId)) {
            throw new \Illuminate\Auth\Access\AuthorizationException("Insufficient role in clinic. Required: {$role} in clinic ID: {$clinicId}");
        }
    }

    /**
     * Return a forbidden response
     */
    protected function forbiddenResponse(string $message = 'Access forbidden'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * Handle exceptions and return appropriate error response
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        // Log the exception
    Log::error('API Exception: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        // Return appropriate error response based on exception type
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($e->errors());
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->errorResponse('Unauthenticated', null, 401);
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->forbiddenResponse('Access denied');
        }

        // For other exceptions, return a generic error
        return $this->errorResponse('An error occurred while processing your request', null, 500);
    }

    /**
     * Check if the request is from a mobile device
     */
    protected function isMobileRequest(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone'];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }
}
