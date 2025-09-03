<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\LabResult;
use App\Models\FileAsset;
use App\Models\Encounter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EMRController extends Controller
{
    /**
     * Display patient's complete medical record
     */
    public function medicalRecord(Patient $patient): View
    {
        $patient->load([
            'clinic',
            'encounters.doctor',
            'encounters.prescriptions',
            'labResults.orderedByDoctor',
            'labResults.encounter',
            'fileAssets',
            'appointments.doctor'
        ]);

        // Group lab results by type
        $labResultsByType = $patient->labResults->groupBy('test_type');

        // Group file assets by category
        $filesByCategory = $patient->fileAssets->groupBy('category');

        return view('emr.medical-record', compact('patient', 'labResultsByType', 'filesByCategory'));
    }

    /**
     * Display patient's lab results
     */
    public function labResults(Patient $patient): View
    {
        $labResults = $patient->labResults()
            ->with(['encounter.doctor', 'orderedByDoctor', 'reviewedByDoctor'])
            ->orderBy('ordered_at', 'desc')
            ->paginate(15);

        return view('emr.lab-results', compact('patient', 'labResults'));
    }

    /**
     * Display patient's medical documents
     */
    public function documents(Patient $patient): View
    {
        $documents = $patient->fileAssets()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('emr.documents', compact('patient', 'documents'));
    }

    /**
     * Store a new lab result
     */
    public function storeLabResult(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'test_type' => 'required|string|max:255',
            'test_name' => 'required|string|max:255',
            'result_value' => 'nullable|string|max:500',
            'unit' => 'nullable|string|max:100',
            'reference_range' => 'nullable|string|max:255',
            'status' => 'required|in:pending,completed,abnormal,critical',
            'ordered_at' => 'required|date',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'encounter_id' => 'nullable|exists:encounters,id',
        ]);

        $validated['clinic_id'] = $patient->clinic_id;
        $validated['patient_id'] = $patient->id;
        $validated['ordered_by_doctor_id'] = auth()->user()->doctorRecords->first()?->id;

        LabResult::create($validated);

        return redirect()->route('emr.lab-results', $patient)
            ->with('success', 'Lab result created successfully.');
    }

    /**
     * Update a lab result
     */
    public function updateLabResult(Request $request, LabResult $labResult): RedirectResponse
    {
        $validated = $request->validate([
            'result_value' => 'nullable|string|max:500',
            'unit' => 'nullable|string|max:100',
            'reference_range' => 'nullable|string|max:255',
            'status' => 'required|in:pending,completed,abnormal,critical',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'reviewed_by_doctor_id' => 'nullable|exists:doctors,id',
        ]);

        if ($validated['status'] === 'completed' && !$validated['completed_at']) {
            $validated['completed_at'] = now();
        }

        if (!$labResult->reviewed_by_doctor_id) {
            $validated['reviewed_by_doctor_id'] = auth()->user()->doctorRecords->first()?->id;
        }

        $labResult->update($validated);

        return redirect()->route('emr.lab-results', $labResult->patient)
            ->with('success', 'Lab result updated successfully.');
    }

    /**
     * Store a new medical document
     */
    public function storeDocument(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'encounter_id' => 'nullable|exists:encounters,id',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('medical-files', $fileName, 'public');

        FileAsset::create([
            'clinic_id' => $patient->clinic_id,
            'owner_type' => Patient::class,
            'owner_id' => $patient->id,
            'url' => $filePath,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'checksum' => md5_file($file->getRealPath()),
            'category' => $validated['category'],
            'description' => $validated['description'],
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('emr.documents', $patient)
            ->with('success', 'Medical document uploaded successfully.');
    }

    /**
     * Download a medical document
     */
    public function downloadDocument(FileAsset $fileAsset)
    {
        // Check if user has permission to access this file
        $user = auth()->user();
        if (!$user->hasAllPermissionsInClinic(['medical_records.view'], $fileAsset->clinic_id)) {
            abort(403, 'Insufficient permissions to access this document.');
        }

        $path = storage_path('app/public/' . $fileAsset->url);

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, $fileAsset->original_name);
    }

    /**
     * Delete a medical document
     */
    public function deleteDocument(FileAsset $fileAsset): RedirectResponse
    {
        $patient = $fileAsset->owner;

        // Check if user has permission to delete this file
        $user = auth()->user();
        if (!$user->hasAllPermissionsInClinic(['medical_records.delete'], $fileAsset->clinic_id)) {
            abort(403, 'Insufficient permissions to delete this document.');
        }

        // Delete the physical file
        $path = storage_path('app/public/' . $fileAsset->url);
        if (file_exists($path)) {
            unlink($path);
        }

        $fileAsset->delete();

        return redirect()->route('emr.documents', $patient)
            ->with('success', 'Medical document deleted successfully.');
    }
}
