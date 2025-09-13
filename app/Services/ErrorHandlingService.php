<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ErrorHandlingService
{
    /**
     * Handle API errors with proper formatting
     */
    public static function handleApiError(Throwable $exception, string $context = ''): JsonResponse
    {
        $errorId = uniqid('err_', true);

        // Log the error with context
        Log::error('API Error: ' . $exception->getMessage(), [
            'error_id' => $errorId,
            'context' => $context,
            'exception' => $exception,
            'trace' => $exception->getTraceAsString()
        ]);

        // Handle different types of exceptions
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
                'error_id' => $errorId
            ], 422);
        }

        if ($exception instanceof HttpException) {
            return response()->json([
                'success' => false,
                'error' => 'HTTP Error',
                'message' => $exception->getMessage(),
                'error_id' => $errorId
            ], $exception->getStatusCode());
        }

        // Default error response
        $statusCode = 500;
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }

        return response()->json([
            'success' => false,
            'error' => 'Internal Server Error',
            'message' => app()->environment('production')
                ? 'An unexpected error occurred. Please try again later.'
                : $exception->getMessage(),
            'error_id' => $errorId
        ], $statusCode);
    }

    /**
     * Handle validation errors
     */
    public static function handleValidationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Validation Error',
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    /**
     * Handle permission errors
     */
    public static function handlePermissionError(string $message = 'Insufficient permissions'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Permission Denied',
            'message' => $message
        ], 403);
    }

    /**
     * Handle not found errors
     */
    public static function handleNotFoundError(string $resource = 'Resource'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Not Found',
            'message' => "{$resource} not found"
        ], 404);
    }

    /**
     * Handle authentication errors
     */
    public static function handleAuthError(string $message = 'Authentication required'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Unauthenticated',
            'message' => $message
        ], 401);
    }

    /**
     * Handle clinic access errors
     */
    public static function handleClinicAccessError(string $message = 'No access to this clinic'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Clinic Access Denied',
            'message' => $message
        ], 403);
    }

    /**
     * Handle license errors
     */
    public static function handleLicenseError(string $message = 'License validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'License Error',
            'message' => $message
        ], 403);
    }

    /**
     * Handle file upload errors
     */
    public static function handleFileUploadError(string $message = 'File upload failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'File Upload Error',
            'message' => $message
        ], 400);
    }

    /**
     * Handle database errors
     */
    public static function handleDatabaseError(Throwable $exception): JsonResponse
    {
        $errorId = uniqid('db_err_', true);

        Log::error('Database Error: ' . $exception->getMessage(), [
            'error_id' => $errorId,
            'exception' => $exception
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Database Error',
            'message' => 'A database error occurred. Please try again later.',
            'error_id' => $errorId
        ], 500);
    }

    /**
     * Handle external service errors
     */
    public static function handleExternalServiceError(string $service, string $message = 'External service error'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'External Service Error',
            'message' => "{$service}: {$message}"
        ], 502);
    }

    /**
     * Create a success response
     */
    public static function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create a paginated response
     */
    public static function paginatedResponse($data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem()
            ]
        ]);
    }
}
