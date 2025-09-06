<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\FileAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class FileAssetController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/file-assets",
     *     summary="Get all file assets",
     *     description="Retrieve a paginated list of file assets for the current clinic",
     *     tags={"File Assets"},
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
     *         description="Search by file name, original name, or description",
     *         @OA\Schema(type="string", example="medical report")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         @OA\Schema(type="string", example="medical_document")
     *     ),
     *     @OA\Parameter(
     *         name="mime_type",
     *         in="query",
     *         description="Filter by MIME type",
     *         @OA\Schema(type="string", example="image")
     *     ),
     *     @OA\Parameter(
     *         name="owner_type",
     *         in="query",
     *         description="Filter by owner type",
     *         @OA\Schema(type="string", enum={"App\\Models\\Patient","App\\Models\\Encounter","App\\Models\\LabResult","App\\Models\\Prescription"})
     *     ),
     *     @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         description="Filter by owner ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"id","file_name","category","size","created_at"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File assets retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File assets retrieved successfully"),
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
     *                                 @OA\Property(property="file_name", type="string", example="document_123.pdf"),
     *                                 @OA\Property(property="original_name", type="string", example="Medical Report.pdf"),
     *                                 @OA\Property(property="category", type="string", example="medical_document"),
     *                                 @OA\Property(property="mime", type="string", example="application/pdf"),
     *                                 @OA\Property(property="size", type="integer", example=1024000),
     *                                 @OA\Property(property="path", type="string", example="files/App\\Models\\Patient/1/document_123.pdf"),
     *                                 @OA\Property(property="description", type="string", example="Patient medical report"),
     *                                 @OA\Property(property="owner_type", type="string", example="App\\Models\\Patient"),
     *                                 @OA\Property(property="owner_id", type="integer", example=1),
     *                                 @OA\Property(property="clinic_id", type="integer", example=1),
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
     *         description="No clinic access",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            [$perPage, $page] = $this->getPaginationParams($request);
            [$sort, $direction] = $this->getSortingParams($request, [
                'id', 'file_name', 'category', 'size', 'created_at'
            ]);

            $query = FileAsset::where('clinic_id', $currentClinic->id)
                ->with(['owner']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('file_name', 'like', "%{$search}%")
                      ->orWhere('original_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($request->has('category')) {
                $query->where('category', $request->get('category'));
            }

            // Filter by file type
            if ($request->has('mime_type')) {
                $query->where('mime', 'like', $request->get('mime_type') . '%');
            }

            // Filter by owner type
            if ($request->has('owner_type')) {
                $query->where('owner_type', $request->get('owner_type'));
            }

            // Filter by owner ID
            if ($request->has('owner_id')) {
                $query->where('owner_id', $request->get('owner_id'));
            }

            $fileAssets = $query->orderBy($sort, $direction)
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($fileAssets, 'File assets retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/file-assets",
     *     summary="Upload new file asset",
     *     description="Upload a new file asset to the system",
     *     tags={"File Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary", description="File to upload"),
     *                 @OA\Property(property="title", type="string", example="Patient X-Ray Report", description="File title"),
     *                 @OA\Property(property="description", type="string", example="Chest X-ray for patient John Doe", description="File description"),
     *                 @OA\Property(property="category", type="string", example="medical_images", description="File category"),
     *                 @OA\Property(property="patient_id", type="integer", example=1, description="Associated patient ID"),
     *                 @OA\Property(property="doctor_id", type="integer", example=1, description="Associated doctor ID"),
     *                 @OA\Property(property="appointment_id", type="integer", example=1, description="Associated appointment ID"),
     *                 @OA\Property(property="encounter_id", type="integer", example=1, description="Associated encounter ID"),
     *                 @OA\Property(property="is_private", type="boolean", example=false, description="Whether file is private"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"x-ray","chest","diagnostic"}, description="File tags")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File asset uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File asset uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="file_asset", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Patient X-Ray Report"),
     *                     @OA\Property(property="description", type="string", example="Chest X-ray for patient John Doe"),
     *                     @OA\Property(property="filename", type="string", example="xray_chest_20240115.jpg"),
     *                     @OA\Property(property="original_filename", type="string", example="chest_xray.jpg"),
     *                     @OA\Property(property="file_size", type="integer", example=2048576),
     *                     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                     @OA\Property(property="category", type="string", example="medical_images"),
     *                     @OA\Property(property="file_path", type="string", example="/storage/files/xray_chest_20240115.jpg"),
     *                     @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                     @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                     @OA\Property(property="is_private", type="boolean", example=false),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
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
    public function store(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
                'category' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'owner_type' => 'required|string|in:App\Models\Patient,App\Models\Encounter,App\Models\LabResult,App\Models\Prescription',
                'owner_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $file = $request->file('file');
            $path = $file->store('files/' . $request->owner_type . '/' . $request->owner_id, 'private');

            $fileAsset = FileAsset::create([
                'clinic_id' => $currentClinic->id,
                'owner_type' => $request->owner_type,
                'owner_id' => $request->owner_id,
                'url' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'checksum' => md5_file($file->getPathname()),
                'category' => $request->category,
                'description' => $request->description,
                'file_name' => $file->hashName(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            $fileAsset->load(['owner']);

            return $this->successResponse([
                'file_asset' => $fileAsset,
            ], 'File uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/file-assets/{fileAsset}",
     *     summary="Get file asset details",
     *     description="Retrieve detailed information about a specific file asset",
     *     tags={"File Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="fileAsset",
     *         in="path",
     *         description="File asset ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File asset details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File asset retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="file_asset", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Patient X-Ray Report"),
     *                     @OA\Property(property="description", type="string", example="Chest X-ray for patient John Doe"),
     *                     @OA\Property(property="filename", type="string", example="xray_chest_20240115.jpg"),
     *                     @OA\Property(property="original_filename", type="string", example="chest_xray.jpg"),
     *                     @OA\Property(property="file_size", type="integer", example=2048576),
     *                     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                     @OA\Property(property="category", type="string", example="medical_images"),
     *                     @OA\Property(property="file_path", type="string", example="/storage/files/xray_chest_20240115.jpg"),
     *                     @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                     @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                     @OA\Property(property="is_private", type="boolean", example=false),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="patient", type="object", nullable=true),
     *                     @OA\Property(property="doctor", type="object", nullable=true),
     *                     @OA\Property(property="appointment", type="object", nullable=true),
     *                     @OA\Property(property="encounter", type="object", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(
     *                     property="metadata",
     *                     type="object",
     *                     @OA\Property(property="download_count", type="integer", example=15),
     *                     @OA\Property(property="last_accessed", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
     *                     @OA\Property(property="access_permissions", type="array", @OA\Items(type="string"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this file asset",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File asset not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show(FileAsset $fileAsset): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($fileAsset->clinic_id)) {
                return $this->forbiddenResponse('No access to this file');
            }

            $fileAsset->load(['owner']);

            return $this->successResponse([
                'file_asset' => $fileAsset,
                'is_image' => $fileAsset->isImage(),
                'is_pdf' => $fileAsset->isPdf(),
                'is_document' => $fileAsset->isDocument(),
                'human_size' => $fileAsset->human_size,
                'extension' => $fileAsset->extension,
            ], 'File asset retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified file asset
     */
    public function update(Request $request, FileAsset $fileAsset): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($fileAsset->clinic_id)) {
                return $this->forbiddenResponse('No access to this file');
            }

            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $fileAsset->update($validator->validated());
            $fileAsset->load(['owner']);

            return $this->successResponse([
                'file_asset' => $fileAsset,
            ], 'File asset updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified file asset
     */
    public function destroy(FileAsset $fileAsset): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($fileAsset->clinic_id)) {
                return $this->forbiddenResponse('No access to this file');
            }

            // Delete the file from storage
            if (Storage::disk('private')->exists($fileAsset->url)) {
                Storage::disk('private')->delete($fileAsset->url);
            }

            $fileAsset->delete();

            return $this->successResponse(null, 'File asset deleted successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/file-assets/{fileAsset}/download",
     *     summary="Download file",
     *     description="Download a file asset",
     *     tags={"File Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="fileAsset",
     *         in="path",
     *         description="File asset ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File download initiated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File download initiated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="download_url", type="string", example="/api/v1/file-assets/1/download"),
     *                 @OA\Property(property="filename", type="string", example="xray_chest_20240115.jpg"),
     *                 @OA\Property(property="file_size", type="integer", example=2048576),
     *                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-15T11:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this file asset",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File asset not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function download(FileAsset $fileAsset): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($fileAsset->clinic_id)) {
                return $this->forbiddenResponse('No access to this file');
            }

            if (!Storage::disk('private')->exists($fileAsset->url)) {
                return $this->notFoundResponse('File not found');
            }

            $downloadUrl = Storage::disk('private')->temporaryUrl(
                $fileAsset->url,
                now()->addMinutes(30)
            );

            return $this->successResponse([
                'download_url' => $downloadUrl,
                'file_name' => $fileAsset->original_name,
                'mime_type' => $fileAsset->mime,
                'size' => $fileAsset->size,
            ], 'Download URL generated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/file-assets/{fileAsset}/preview",
     *     summary="Preview file",
     *     description="Get a preview of a file asset",
     *     tags={"File Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="fileAsset",
     *         in="path",
     *         description="File asset ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="Preview size",
     *         @OA\Schema(type="string", enum={"thumbnail","small","medium","large"}, example="medium")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File preview retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File preview retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="preview_url", type="string", example="/api/v1/file-assets/1/preview"),
     *                 @OA\Property(property="preview_type", type="string", example="image"),
     *                 @OA\Property(property="preview_size", type="string", example="medium"),
     *                 @OA\Property(property="can_preview", type="boolean", example=true),
     *                 @OA\Property(property="preview_dimensions", type="object",
     *                     @OA\Property(property="width", type="integer", example=800),
     *                     @OA\Property(property="height", type="integer", example=600)
     *                 ),
     *                 @OA\Property(property="alternative_preview", type="string", example="File cannot be previewed directly. Please download to view.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this file asset",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File asset not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function preview(FileAsset $fileAsset): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($fileAsset->clinic_id)) {
                return $this->forbiddenResponse('No access to this file');
            }

            if (!Storage::disk('private')->exists($fileAsset->url)) {
                return $this->notFoundResponse('File not found');
            }

            $previewUrl = Storage::disk('private')->temporaryUrl(
                $fileAsset->url,
                now()->addMinutes(30)
            );

            return $this->successResponse([
                'preview_url' => $previewUrl,
                'file_name' => $fileAsset->original_name,
                'mime_type' => $fileAsset->mime,
                'size' => $fileAsset->size,
                'is_image' => $fileAsset->isImage(),
                'is_pdf' => $fileAsset->isPdf(),
                'is_document' => $fileAsset->isDocument(),
            ], 'Preview URL generated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Upload file
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
                'category' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'owner_type' => 'required|string|in:App\Models\Patient,App\Models\Encounter,App\Models\LabResult,App\Models\Prescription',
                'owner_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $file = $request->file('file');
            $path = $file->store('files/' . $request->owner_type . '/' . $request->owner_id, 'private');

            $fileAsset = FileAsset::create([
                'clinic_id' => $currentClinic->id,
                'owner_type' => $request->owner_type,
                'owner_id' => $request->owner_id,
                'url' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'checksum' => md5_file($file->getPathname()),
                'category' => $request->category,
                'description' => $request->description,
                'file_name' => $file->hashName(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            $fileAsset->load(['owner']);

            return $this->successResponse([
                'file_asset' => $fileAsset,
            ], 'File uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get file categories
     */
    public function categories(): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $categories = FileAsset::where('clinic_id', $currentClinic->id)
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values();

            return $this->successResponse([
                'categories' => $categories,
            ], 'File categories retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/file-assets/{fileAsset}/share",
     *     summary="Share file",
     *     description="Create a shareable link for a file asset",
     *     tags={"File Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="fileAsset",
     *         in="path",
     *         description="File asset ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-22T10:00:00Z", description="Share expiration date"),
     *             @OA\Property(property="password", type="string", example="secure123", description="Optional password for the share"),
     *             @OA\Property(property="max_downloads", type="integer", example=10, description="Maximum number of downloads"),
     *             @OA\Property(property="allow_preview", type="boolean", example=true, description="Allow preview without download"),
     *             @OA\Property(property="notify_on_access", type="boolean", example=true, description="Notify when file is accessed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File share created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File share created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="share_id", type="string", example="share_abc123def456"),
     *                 @OA\Property(property="share_url", type="string", example="https://medinext.com/shared/abc123def456"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2024-01-22T10:00:00Z"),
     *                 @OA\Property(property="max_downloads", type="integer", example=10),
     *                 @OA\Property(property="download_count", type="integer", example=0),
     *                 @OA\Property(property="is_password_protected", type="boolean", example=true),
     *                 @OA\Property(property="allow_preview", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No access to this file asset",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File asset not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function share(Request $request, FileAsset $fileAsset): JsonResponse
    {
        try {
            if (!$this->hasClinicAccess($fileAsset->clinic_id)) {
                return $this->forbiddenResponse('No access to this file');
            }

            $validator = Validator::make($request->all(), [
                'expires_at' => 'nullable|date|after:now',
                'password' => 'nullable|string|min:6|max:255',
                'max_downloads' => 'nullable|integer|min:1|max:1000',
                'allow_preview' => 'nullable|boolean',
                'notify_on_access' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Mock share creation
            $shareData = [
                'share_id' => 'share_' . uniqid(),
                'share_url' => 'https://medinext.com/shared/' . uniqid(),
                'expires_at' => $request->get('expires_at', now()->addDays(7)->toISOString()),
                'max_downloads' => $request->get('max_downloads', 10),
                'download_count' => 0,
                'is_password_protected' => !empty($request->get('password')),
                'allow_preview' => $request->get('allow_preview', true),
                'created_at' => now()->toISOString()
            ];

            return $this->successResponse($shareData, 'File share created successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/file-assets/storage-usage",
     *     summary="Get storage usage",
     *     description="Retrieve storage usage statistics for the clinic",
     *     tags={"File Assets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for usage statistics",
     *         @OA\Schema(type="string", enum={"day","week","month","year"}, example="month")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Storage usage retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Storage usage retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="period", type="string", example="month"),
     *                 @OA\Property(property="overview", type="object",
     *                     @OA\Property(property="total_files", type="integer", example=1250),
     *                     @OA\Property(property="total_size", type="integer", example=5242880000, description="Total size in bytes"),
     *                     @OA\Property(property="total_size_formatted", type="string", example="4.88 GB"),
     *                     @OA\Property(property="available_space", type="integer", example=10737418240, description="Available space in bytes"),
     *                     @OA\Property(property="available_space_formatted", type="string", example="10.00 GB"),
     *                     @OA\Property(property="usage_percentage", type="number", format="float", example=48.8)
     *                 ),
     *                 @OA\Property(property="by_category", type="array", @OA\Items(
     *                     @OA\Property(property="category", type="string", example="medical_images"),
     *                     @OA\Property(property="file_count", type="integer", example=450),
     *                     @OA\Property(property="total_size", type="integer", example=2097152000),
     *                     @OA\Property(property="total_size_formatted", type="string", example="2.00 GB"),
     *                     @OA\Property(property="percentage", type="number", format="float", example=40.0)
     *                 )),
     *                 @OA\Property(property="trends", type="object",
     *                     @OA\Property(property="growth_rate", type="number", format="float", example=15.2),
     *                     @OA\Property(property="projected_full_date", type="string", format="date", example="2024-08-15"),
     *                     @OA\Property(property="monthly_growth", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(property="recommendations", type="array", @OA\Items(type="string"), example={"Consider archiving old files","Upgrade storage plan","Clean up duplicate files"})
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
    public function storageUsage(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $period = $request->get('period', 'month');

            // Mock storage usage data
            $storageUsage = [
                'period' => $period,
                'overview' => [
                    'total_files' => 1250,
                    'total_size' => 5242880000, // 4.88 GB in bytes
                    'total_size_formatted' => '4.88 GB',
                    'available_space' => 10737418240, // 10 GB in bytes
                    'available_space_formatted' => '10.00 GB',
                    'usage_percentage' => 48.8
                ],
                'by_category' => [
                    [
                        'category' => 'medical_images',
                        'file_count' => 450,
                        'total_size' => 2097152000,
                        'total_size_formatted' => '2.00 GB',
                        'percentage' => 40.0
                    ],
                    [
                        'category' => 'documents',
                        'file_count' => 600,
                        'total_size' => 1572864000,
                        'total_size_formatted' => '1.50 GB',
                        'percentage' => 30.0
                    ],
                    [
                        'category' => 'lab_results',
                        'file_count' => 200,
                        'total_size' => 1048576000,
                        'total_size_formatted' => '1.00 GB',
                        'percentage' => 20.0
                    ]
                ],
                'trends' => [
                    'growth_rate' => 15.2,
                    'projected_full_date' => '2024-08-15',
                    'monthly_growth' => []
                ],
                'recommendations' => [
                    'Consider archiving old files',
                    'Upgrade storage plan',
                    'Clean up duplicate files'
                ]
            ];

            return $this->successResponse($storageUsage, 'Storage usage retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
