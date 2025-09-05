<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to view patients
        if (!$this->user()->hasPermissionInClinic('patient.read', $clinicId)) {
            abort(403, 'Insufficient permissions to view patients');
        }

        $query = Patient::with(['clinic', 'appointments', 'encounters', 'prescriptions'])
            ->withCount(['appointments', 'encounters', 'prescriptions']);

        // Filter by clinic if user is not super admin
        if (!$this->user()->isSuperAdmin()) {
            $query->where('clinic_id', $clinicId);
        }

        $patients = $query->paginate(12);

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to create patients
        if (!$this->user()->hasPermissionInClinic('patient.write', $clinicId)) {
            abort(403, 'Insufficient permissions to create patients');
        }

        $clinics = $this->user()->isSuperAdmin() ? Clinic::all() : Clinic::where('id', $clinicId)->get();
        return view('patients.create', compact('clinics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to create patients
        if (!$this->user()->hasPermissionInClinic('patient.write', $clinicId)) {
            abort(403, 'Insufficient permissions to create patients');
        }

        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'code' => 'required|string|max:50|unique:patients',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'sex' => 'required|in:male,female,other',
            'contact' => 'nullable|array',
            'allergies' => 'nullable|array',
            'consents' => 'nullable|array',
        ]);

        // Ensure user can only create patients in their clinic (unless super admin)
        if (!$this->user()->isSuperAdmin() && $validated['clinic_id'] != $clinicId) {
            abort(403, 'Cannot create patients in other clinics');
        }

        Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Patient $patient): View
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to view patients
        if (!$this->user()->hasPermissionInClinic('patient.read', $clinicId)) {
            abort(403, 'Insufficient permissions to view patients');
        }

        // Ensure user can only view patients from their clinic (unless super admin)
        if (!$this->user()->isSuperAdmin() && $patient->clinic_id != $clinicId) {
            abort(403, 'Cannot view patients from other clinics');
        }

        $patient->load(['clinic', 'appointments.doctor', 'encounters.doctor', 'prescriptions']);
        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Patient $patient): View
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to edit patients
        if (!$this->user()->hasPermissionInClinic('patient.write', $clinicId)) {
            abort(403, 'Insufficient permissions to edit patients');
        }

        // Ensure user can only edit patients from their clinic (unless super admin)
        if (!$this->user()->isSuperAdmin() && $patient->clinic_id != $clinicId) {
            abort(403, 'Cannot edit patients from other clinics');
        }

        $clinics = $this->user()->isSuperAdmin() ? Clinic::all() : Clinic::where('id', $clinicId)->get();
        return view('patients.edit', compact('patient', 'clinics'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to edit patients
        if (!$this->user()->hasPermissionInClinic('patient.write', $clinicId)) {
            abort(403, 'Insufficient permissions to edit patients');
        }

        // Ensure user can only edit patients from their clinic (unless super admin)
        if (!$this->user()->isSuperAdmin() && $patient->clinic_id != $clinicId) {
            abort(403, 'Cannot edit patients from other clinics');
        }

        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'code' => 'required|string|max:50|unique:patients,code,' . $patient->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'sex' => 'required|in:male,female,other',
            'contact' => 'nullable|array',
            'allergies' => 'nullable|array',
            'consents' => 'nullable|array',
        ]);

        // Ensure user can only move patients within their clinic (unless super admin)
        if (!$this->user()->isSuperAdmin() && $validated['clinic_id'] != $clinicId) {
            abort(403, 'Cannot move patients to other clinics');
        }

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Patient $patient)
    {
        $clinicId = $this->getClinicId($request);

        // Check if user has permission to delete patients
        if (!$this->user()->hasPermissionInClinic('patient.delete', $clinicId)) {
            abort(403, 'Insufficient permissions to delete patients');
        }

        // Ensure user can only delete patients from their clinic (unless super admin)
        if (!$this->user()->isSuperAdmin() && $patient->clinic_id != $clinicId) {
            abort(403, 'Cannot delete patients from other clinics');
        }

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }

    /**
     * Get the authenticated user
     */
    protected function user()
    {
        return Auth::user();
    }

    /**
     * Get clinic ID from request
     */
    protected function getClinicId(Request $request): ?int
    {
        $clinicId = $request->route('clinic_id')
            ?? $request->input('clinic_id')
            ?? $request->header('X-Clinic-ID')
            ?? session('current_clinic_id');

        return $clinicId ? (int) $clinicId : null;
    }
}
