<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'clinic']);

        // Filter by clinic if specified
        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $doctors = $query->paginate(15);

        $clinics = Clinic::all();

        return view('doctors.index', compact('doctors', 'clinics'));
    }

    /**
     * Show the form for creating a new doctor
     */
    public function create()
    {
        $clinics = Clinic::all();
        $users = User::whereDoesntHave('doctorRecords')->get();

        return view('doctors.create', compact('clinics', 'users'));
    }

    /**
     * Store a newly created doctor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:doctors,user_id',
            'clinic_id' => 'required|exists:clinics,id',
            'specialty' => 'nullable|string|max:255',
            'license_no' => 'nullable|string|max:100',
            'signature_url' => 'nullable|url|max:500',
        ]);

        // Check if user is already a doctor in this clinic
        $existing = Doctor::where('user_id', $validated['user_id'])
            ->where('clinic_id', $validated['clinic_id'])
            ->first();

        if ($existing) {
            return back()->with('error', 'This user is already a doctor in this clinic.');
        }

        Doctor::create($validated);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor added successfully.');
    }

    /**
     * Display the specified doctor
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'clinic', 'appointments', 'encounters']);

        $stats = [
            'total_appointments' => $doctor->appointments()->count(),
            'total_encounters' => $doctor->encounters()->count(),
            'total_prescriptions' => $doctor->prescriptions()->count(),
        ];

        return view('doctors.show', compact('doctor', 'stats'));
    }

    /**
     * Show the form for editing the specified doctor
     */
    public function edit(Doctor $doctor)
    {
        $clinics = Clinic::all();

        return view('doctors.edit', compact('doctor', 'clinics'));
    }

    /**
     * Update the specified doctor
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'specialty' => 'nullable|string|max:255',
            'license_no' => 'nullable|string|max:100',
            'signature_url' => 'nullable|url|max:500',
        ]);

        $doctor->update($validated);

        return redirect()->route('doctors.show', $doctor)
            ->with('success', 'Doctor updated successfully.');
    }

    /**
     * Remove the specified doctor
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor removed successfully.');
    }

    /**
     * Show doctor's schedule
     */
    public function schedule(Doctor $doctor)
    {
        $appointments = $doctor->appointments()
            ->with(['patient', 'room'])
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();

        return view('doctors.schedule', compact('doctor', 'appointments'));
    }

    /**
     * Show doctor's patients
     */
    public function patients(Doctor $doctor)
    {
        $patients = $doctor->clinic->patients()
            ->whereHas('appointments', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            })
            ->withCount(['appointments' => function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            }])
            ->paginate(15);

        return view('doctors.patients', compact('doctor', 'patients'));
    }
}
