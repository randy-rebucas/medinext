<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $clinicId = session('current_clinic_id') ?? $user->clinics->first()?->id;

        if (!$clinicId) {
            abort(403, 'No clinic access.');
        }

        $appointments = Appointment::where('clinic_id', $clinicId)
            ->with(['patient', 'doctor', 'clinic', 'room'])
            ->orderBy('start_at', 'asc')
            ->paginate(12);

        return view('appointments.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $patients = Patient::all();
        $doctors = Doctor::all();
        $clinics = Clinic::all();
        $rooms = Room::all();

        return view('appointments.create', compact('patients', 'doctors', 'clinics', 'rooms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled',
            'room_id' => 'nullable|exists:rooms,id',
            'reason' => 'nullable|string|max:500',
            'source' => 'nullable|string|max:100',
        ]);

        Appointment::create($validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor', 'clinic', 'room']);
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment): View
    {
        $patients = Patient::all();
        $doctors = Doctor::all();
        $clinics = Clinic::all();
        $rooms = Room::all();

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'clinics', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled',
            'room_id' => 'nullable|exists:rooms,id',
            'reason' => 'nullable|string|max:500',
            'source' => 'nullable|string|max:100',
        ]);

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
