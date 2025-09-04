<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    /**
     * Success response format
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Error response format
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors): JsonResponse
    {
        return $this->errorResponse('Validation failed', $errors, 422);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * Get the authenticated user
     */
    protected function getAuthenticatedUser()
    {
        return Auth::user();
    }

    /**
     * Get the user's current clinic
     */
    protected function getCurrentClinic()
    {
        $user = $this->getAuthenticatedUser();
        return $user->clinics()->first();
    }

    /**
     * Check if user has access to clinic
     */
    protected function hasClinicAccess($clinicId): bool
    {
        $user = $this->getAuthenticatedUser();
        return $user->clinics()->where('clinic_id', $clinicId)->exists();
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $this->getAuthenticatedUser();
        return $user->hasPermission($permission);
    }

    /**
     * Validate request data
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Paginate response
     */
    protected function paginatedResponse($data, string $message = 'Success'): JsonResponse
    {
        return $this->successResponse([
            'items' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
            ]
        ], $message);
    }

    /**
     * Handle exceptions
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($e->errors());
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFoundResponse('Resource not found');
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->forbiddenResponse($e->getMessage());
        }

        // Log the exception for debugging
        \Log::error('API Exception: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);

        return $this->errorResponse('Internal server error', null, 500);
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'per_page' => min($request->get('per_page', 15), 100), // Max 100 items per page
            'page' => max($request->get('page', 1), 1),
        ];
    }

    /**
     * Get sorting parameters from request
     */
    protected function getSortingParams(Request $request, array $allowedSorts = [], string $defaultSort = 'created_at'): array
    {
        $sort = $request->get('sort', $defaultSort);
        $direction = $request->get('direction', 'desc');

        // Validate sort field
        if (!empty($allowedSorts) && !in_array($sort, $allowedSorts)) {
            $sort = $defaultSort;
        }

        // Validate direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        return [$sort, $direction];
    }

    /**
     * Get filter parameters from request
     */
    protected function getFilterParams(Request $request, array $allowedFilters = []): array
    {
        $filters = [];

        foreach ($allowedFilters as $filter) {
            if ($request->has($filter)) {
                $filters[$filter] = $request->get($filter);
            }
        }

        return $filters;
    }

    /**
     * Transform data using a transformer
     */
    protected function transform($data, $transformer = null)
    {
        if (!$transformer) {
            return $data;
        }

        if (is_array($data) || $data instanceof \Illuminate\Support\Collection) {
            return $data->map(function ($item) use ($transformer) {
                return new $transformer($item);
            });
        }

        return new $transformer($data);
    }

    /**
     * Get API version from request
     */
    protected function getApiVersion(Request $request): string
    {
        return $request->header('API-Version', 'v1');
    }

    /**
     * Check if request is from mobile app
     */
    protected function isMobileRequest(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        $platform = $request->header('X-Platform', '');

        return str_contains($userAgent, 'Mobile') || 
               str_contains($userAgent, 'Android') || 
               str_contains($userAgent, 'iPhone') ||
               $platform === 'mobile';
    }

    /**
     * Get client information
     */
    protected function getClientInfo(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'platform' => $request->header('X-Platform'),
            'version' => $request->header('X-App-Version'),
            'device_id' => $request->header('X-Device-ID'),
        ];
    }
}
