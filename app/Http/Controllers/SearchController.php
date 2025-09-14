<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Display the search page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Search Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('search', [
                'results' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('search', [
            'results' => [],
            'permissions' => $permissions,
        ]);
    }

    /**
     * Perform global search
     */
    public function search(Request $request)
    {
        try {
            $this->logWebRequest('Global Search', ['action' => 'search', 'query' => $request->get('q')]);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated search attempt');
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
            $query = $request->get('q', '');
            $type = $request->get('type', 'all');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $results = $this->performSearch($clinicId, $query, $type);

            return response()->json([
                'success' => true,
                'results' => $results,
                'query' => $query,
                'total' => count($results)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'SearchController::search');
            return response()->json([
                'success' => false,
                'message' => 'Search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Search patients
     */
    public function searchPatients(Request $request)
    {
        try {
            $user = $request->user();
            $userClinicRole = $this->getUserClinicRole($request);

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $query = $request->get('q', '');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $patients = Patient::where('clinic_id', $clinicId)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('patient_id', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->select('id', 'first_name', 'last_name', 'patient_id', 'dob', 'sex')
                ->limit(20)
                ->get()
                ->map(function ($patient) {
                    return [
                        'id' => $patient->id,
                        'name' => $patient->first_name . ' ' . $patient->last_name,
                        'patient_id' => $patient->patient_id,
                        'dob' => $patient->dob,
                        'sex' => $patient->sex,
                        'url' => "/patients/{$patient->id}",
                        'type' => 'patient'
                    ];
                });

            return response()->json([
                'success' => true,
                'patients' => $patients
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'SearchController::searchPatients');
            return response()->json([
                'success' => false,
                'message' => 'Patient search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Search doctors
     */
    public function searchDoctors(Request $request)
    {
        try {
            $user = $request->user();
            $userClinicRole = $this->getUserClinicRole($request);

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $query = $request->get('q', '');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $doctors = Doctor::where('clinic_id', $clinicId)
                ->whereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->orWhere('specialization', 'like', "%{$query}%")
                ->orWhere('license_number', 'like', "%{$query}%")
                ->with(['user:id,name,email'])
                ->select('id', 'user_id', 'specialization', 'license_number')
                ->limit(20)
                ->get()
                ->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->user->name,
                        'email' => $doctor->user->email,
                        'specialization' => $doctor->specialization,
                        'license_number' => $doctor->license_number,
                        'url' => "/doctors/{$doctor->id}",
                        'type' => 'doctor'
                    ];
                });

            return response()->json([
                'success' => true,
                'doctors' => $doctors
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'SearchController::searchDoctors');
            return response()->json([
                'success' => false,
                'message' => 'Doctor search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Search appointments
     */
    public function searchAppointments(Request $request)
    {
        try {
            $user = $request->user();
            $userClinicRole = $this->getUserClinicRole($request);

            if (!$userClinicRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have clinic access'
                ], 403);
            }

            $clinicId = $userClinicRole->clinic_id;
            $query = $request->get('q', '');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $appointments = Appointment::where('clinic_id', $clinicId)
                ->where(function ($q) use ($query) {
                    $q->where('type', 'like', "%{$query}%")
                      ->orWhere('reason', 'like', "%{$query}%")
                      ->orWhere('notes', 'like', "%{$query}%")
                      ->orWhereHas('patient', function ($patientQuery) use ($query) {
                          $patientQuery->where('first_name', 'like', "%{$query}%")
                                     ->orWhere('last_name', 'like', "%{$query}%");
                      })
                      ->orWhereHas('doctor.user', function ($doctorQuery) use ($query) {
                          $doctorQuery->where('name', 'like', "%{$query}%");
                      });
                })
                ->with(['patient:id,first_name,last_name', 'doctor.user:id,name'])
                ->select('id', 'patient_id', 'doctor_id', 'start_at', 'type', 'status', 'reason')
                ->limit(20)
                ->get()
                ->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'patient_name' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                        'doctor_name' => $appointment->doctor->user->name,
                        'start_at' => $appointment->start_at,
                        'type' => $appointment->type,
                        'status' => $appointment->status,
                        'reason' => $appointment->reason,
                        'url' => "/appointments/{$appointment->id}",
                        'type' => 'appointment'
                    ];
                });

            return response()->json([
                'success' => true,
                'appointments' => $appointments
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'SearchController::searchAppointments');
            return response()->json([
                'success' => false,
                'message' => 'Appointment search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Perform comprehensive search
     */
    private function performSearch($clinicId, $query, $type)
    {
        $results = [];

        if ($type === 'all' || $type === 'patients') {
            $patients = Patient::where('clinic_id', $clinicId)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('patient_id', 'like', "%{$query}%");
                })
                ->select('id', 'first_name', 'last_name', 'patient_id')
                ->limit(10)
                ->get()
                ->map(function ($patient) {
                    return [
                        'type' => 'patient',
                        'id' => $patient->id,
                        'title' => $patient->first_name . ' ' . $patient->last_name,
                        'description' => 'Patient - ' . $patient->patient_id,
                        'url' => "/patients/{$patient->id}",
                        'score' => 1.0
                    ];
                });

            $results = array_merge($results, $patients->toArray());
        }

        if ($type === 'all' || $type === 'doctors') {
            $doctors = Doctor::where('clinic_id', $clinicId)
                ->whereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->orWhere('specialization', 'like', "%{$query}%")
                ->with(['user:id,name'])
                ->select('id', 'user_id', 'specialization')
                ->limit(10)
                ->get()
                ->map(function ($doctor) {
                    return [
                        'type' => 'doctor',
                        'id' => $doctor->id,
                        'title' => $doctor->user->name,
                        'description' => 'Doctor - ' . $doctor->specialization,
                        'url' => "/doctors/{$doctor->id}",
                        'score' => 0.9
                    ];
                });

            $results = array_merge($results, $doctors->toArray());
        }

        if ($type === 'all' || $type === 'appointments') {
            $appointments = Appointment::where('clinic_id', $clinicId)
                ->where(function ($q) use ($query) {
                    $q->where('type', 'like', "%{$query}%")
                      ->orWhere('reason', 'like', "%{$query}%")
                      ->orWhereHas('patient', function ($patientQuery) use ($query) {
                          $patientQuery->where('first_name', 'like', "%{$query}%")
                                     ->orWhere('last_name', 'like', "%{$query}%");
                      });
                })
                ->with(['patient:id,first_name,last_name'])
                ->select('id', 'patient_id', 'start_at', 'type')
                ->limit(10)
                ->get()
                ->map(function ($appointment) {
                    return [
                        'type' => 'appointment',
                        'id' => $appointment->id,
                        'title' => $appointment->type . ' - ' . $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                        'description' => 'Appointment - ' . $appointment->start_at->format('M d, Y'),
                        'url' => "/appointments/{$appointment->id}",
                        'score' => 0.8
                    ];
                });

            $results = array_merge($results, $appointments->toArray());
        }

        // Sort by score (highest first)
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, 20); // Limit to 20 results
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'global_search', 'search_patients', 'search_doctors', 'search_appointments',
                'search_prescriptions', 'search_users'
            ],
            'admin' => [
                'global_search', 'search_patients', 'search_doctors', 'search_appointments',
                'search_prescriptions'
            ],
            'doctor' => [
                'global_search', 'search_patients', 'search_appointments', 'search_prescriptions'
            ],
            'receptionist' => [
                'global_search', 'search_patients', 'search_doctors', 'search_appointments'
            ],
            'patient' => [
                'search_appointments', 'search_prescriptions'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
