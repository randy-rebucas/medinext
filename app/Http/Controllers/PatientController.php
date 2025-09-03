<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $patients = Patient::with(['clinic', 'appointments', 'encounters', 'prescriptions'])
            ->withCount(['appointments', 'encounters', 'prescriptions'])
            ->paginate(12);

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clinics = Clinic::all();
        return view('patients.create', compact('clinics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): View
    {
        $patient->load(['clinic', 'appointments.doctor', 'encounters.doctor', 'prescriptions']);
        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient): View
    {
        $clinics = Clinic::all();
        return view('patients.edit', compact('patient', 'clinics'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
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

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}
