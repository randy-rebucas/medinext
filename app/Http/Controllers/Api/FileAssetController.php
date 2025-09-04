<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\FileAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileAssetController extends BaseController
{
    /**
     * Display a listing of file assets
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
     * Store a newly created file asset
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
     * Display the specified file asset
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
     * Download file
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
     * Preview file
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
}
