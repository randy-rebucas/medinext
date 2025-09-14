<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\FileAsset;
use App\Models\Patient;
use App\Models\Encounter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

class FileAssetController extends Controller
{
    /**
     * Display the file assets management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('File Assets Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/file-assets', [
                'fileAssets' => [],
                'patients' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get file assets for this clinic
        $fileAssets = $this->getFileAssets($clinicId);

        // Get patients for dropdown
        $patients = $this->getPatients($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/file-assets', [
            'fileAssets' => $fileAssets,
            'patients' => $patients,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly uploaded file
     */
    public function store(Request $request)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'file' => 'required|file|max:10240', // 10MB max
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'encounter_id.exists' => 'Selected encounter does not exist.',
            'file.required' => 'Please select a file to upload.',
            'file.max' => 'File size cannot exceed 10MB.',
            'category.required' => 'File category is required.',
        ];

        try {
            $validatedData = $this->validateAndSanitize($request, $rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $this->logWebRequest('Upload File Asset', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated file upload attempt');
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

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            
            // Store file
            $path = $file->storeAs('file-assets', $filename, 'public');

            // Create file asset record
            $fileAsset = FileAsset::create([
                'clinic_id' => $clinicId,
                'patient_id' => $validatedData['patient_id'],
                'encounter_id' => $validatedData['encounter_id'] ?? null,
                'original_name' => $originalName,
                'filename' => $filename,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'category' => $validatedData['category'],
                'description' => $validatedData['description'] ?? null,
                'tags' => $validatedData['tags'] ?? [],
                'uploaded_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'fileAsset' => $this->getFileAsset($fileAsset->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'FileAssetController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified file asset
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'encounter_id.exists' => 'Selected encounter does not exist.',
            'category.required' => 'File category is required.',
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
            $fileAsset = FileAsset::findOrFail($id);

            // Update file asset
            $fileAsset->update([
                'patient_id' => $request->patient_id,
                'encounter_id' => $request->encounter_id,
                'category' => $request->category,
                'description' => $request->description,
                'tags' => $request->tags ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File asset updated successfully',
                'fileAsset' => $this->getFileAsset($fileAsset->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'FileAssetController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update file asset. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified file asset
     */
    public function destroy($id)
    {
        try {
            $fileAsset = FileAsset::findOrFail($id);

            // Delete file from storage
            if (Storage::disk('public')->exists($fileAsset->file_path)) {
                Storage::disk('public')->delete($fileAsset->file_path);
            }

            // Delete database record
            $fileAsset->delete();

            return response()->json([
                'success' => true,
                'message' => 'File asset deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'FileAssetController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file asset. Please try again.'
            ], 500);
        }
    }

    /**
     * Download file asset
     */
    public function download($id)
    {
        try {
            $fileAsset = FileAsset::findOrFail($id);

            if (!Storage::disk('public')->exists($fileAsset->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            return Storage::disk('public')->download($fileAsset->file_path, $fileAsset->original_name);

        } catch (\Exception $e) {
            $this->handleException($e, 'FileAssetController::download');
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file. Please try again.'
            ], 500);
        }
    }

    /**
     * Get file assets for a clinic with caching
     */
    private function getFileAssets($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('file_assets', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return FileAsset::where('clinic_id', $clinicId)
                ->with(['patient:id,first_name,last_name', 'encounter:id,date'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($fileAsset) {
                    return [
                        'id' => $fileAsset->id,
                        'original_name' => $fileAsset->original_name,
                        'filename' => $fileAsset->filename,
                        'file_size' => $this->formatFileSize($fileAsset->file_size),
                        'mime_type' => $fileAsset->mime_type,
                        'category' => $fileAsset->category,
                        'description' => $fileAsset->description,
                        'tags' => $fileAsset->tags,
                        'patient_name' => $fileAsset->patient ? $fileAsset->patient->first_name . ' ' . $fileAsset->patient->last_name : null,
                        'patient_id' => $fileAsset->patient_id,
                        'encounter_id' => $fileAsset->encounter_id,
                        'uploaded_by' => $fileAsset->uploaded_by,
                        'created_at' => $fileAsset->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $fileAsset->updated_at->format('Y-m-d H:i:s'),
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
     * Get a single file asset
     */
    private function getFileAsset($fileAssetId)
    {
        $fileAsset = FileAsset::with(['patient', 'encounter'])->findOrFail($fileAssetId);

        return [
            'id' => $fileAsset->id,
            'original_name' => $fileAsset->original_name,
            'filename' => $fileAsset->filename,
            'file_path' => $fileAsset->file_path,
            'file_size' => $this->formatFileSize($fileAsset->file_size),
            'mime_type' => $fileAsset->mime_type,
            'category' => $fileAsset->category,
            'description' => $fileAsset->description,
            'tags' => $fileAsset->tags,
            'patient' => $fileAsset->patient ? [
                'id' => $fileAsset->patient->id,
                'name' => $fileAsset->patient->first_name . ' ' . $fileAsset->patient->last_name,
            ] : null,
            'encounter' => $fileAsset->encounter ? [
                'id' => $fileAsset->encounter->id,
                'date' => $fileAsset->encounter->date,
            ] : null,
            'uploaded_by' => $fileAsset->uploaded_by,
            'created_at' => $fileAsset->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $fileAsset->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_file_assets', 'view_file_assets', 'upload_file_assets',
                'download_file_assets', 'delete_file_assets'
            ],
            'admin' => [
                'manage_file_assets', 'view_file_assets', 'upload_file_assets',
                'download_file_assets', 'delete_file_assets'
            ],
            'doctor' => [
                'view_file_assets', 'upload_file_assets', 'download_file_assets'
            ],
            'receptionist' => [
                'view_file_assets', 'upload_file_assets', 'download_file_assets'
            ],
            'patient' => [
                'view_file_assets', 'download_file_assets'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
