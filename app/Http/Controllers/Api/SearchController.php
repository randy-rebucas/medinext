<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="SearchResult",
 *     type="object",
 *     title="Search Result",
 *     description="Global search result",
 *     @OA\Property(property="type", type="string", example="patient"),
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="John Doe"),
 *     @OA\Property(property="description", type="string", example="Patient - P001"),
 *     @OA\Property(property="url", type="string", example="/patients/1"),
 *     @OA\Property(property="score", type="number", format="float", example=0.95)
 * )
 */


class SearchController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/search/global",
     *     summary="Global search",
     *     description="Perform a global search across all entities in the system",
     *     operationId="globalSearch",
     *     tags={"Search"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results per category",
     *         required=false,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="types",
     *         in="query",
     *         description="Comma-separated list of entity types to search",
     *         required=false,
     *         @OA\Schema(type="string", example="patients,doctors,appointments")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Search results retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="query", type="string", example="John"),
     *                 @OA\Property(property="total_results", type="integer", example=15),
     *                 @OA\Property(
     *                     property="results",
     *                     type="object",
     *                     @OA\Property(
     *                         property="patients",
     *                         type="array",
     *                         @OA\Items(type="object")
     *                     ),
     *                     @OA\Property(
     *                         property="doctors",
     *                         type="array",
     *                         @OA\Items(type="object")
     *                     ),
     *                     @OA\Property(
     *                         property="appointments",
     *                         type="array",
     *                         @OA\Items(type="object")
     *                     ),
     *                     @OA\Property(
     *                         property="prescriptions",
     *                         type="array",
     *                         @OA\Items(type="object")
     *                     ),
     *                     @OA\Property(
     *                         property="users",
     *                         type="array",
     *                         @OA\Items(type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid search query",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function global(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            $limit = $request->get('limit', 5);
            $types = $request->get('types', 'patients,doctors,appointments,prescriptions,users');

            if (empty($query) || strlen($query) < 2) {
                return $this->errorResponse('Search query must be at least 2 characters long', null, 400);
            }

            $searchTypes = explode(',', $types);
            $results = [];
            $totalResults = 0;

            $clinicId = Auth::user()->current_clinic_id ?? 1;

            // Search patients
            if (in_array('patients', $searchTypes)) {
                $patients = Patient::where('clinic_id', $clinicId)
                    ->where(function ($q) use ($query) {
                        $q->where('first_name', 'like', "%{$query}%")
                          ->orWhere('last_name', 'like', "%{$query}%")
                          ->orWhere('patient_number', 'like', "%{$query}%")
                          ->orWhere('phone', 'like', "%{$query}%");
                    })
                    ->limit($limit)
                    ->get()
                    ->map(function ($patient) {
                        return [
                            'type' => 'patient',
                            'id' => $patient->id,
                            'title' => $patient->first_name . ' ' . $patient->last_name,
                            'description' => 'Patient - ' . $patient->patient_number,
                            'url' => "/patients/{$patient->id}",
                            'score' => 1.0
                        ];
                    });

                $results['patients'] = $patients->toArray();
                $totalResults += $patients->count();
            }

            // Search doctors
            if (in_array('doctors', $searchTypes)) {
                $doctors = Doctor::where('clinic_id', $clinicId)
                    ->where(function ($q) use ($query) {
                        $q->where('specialization', 'like', "%{$query}%")
                          ->orWhere('license_number', 'like', "%{$query}%")
                          ->orWhereHas('user', function ($userQuery) use ($query) {
                              $userQuery->where('name', 'like', "%{$query}%");
                          });
                    })
                    ->with('user')
                    ->limit($limit)
                    ->get()
                    ->map(function ($doctor) {
                        return [
                            'type' => 'doctor',
                            'id' => $doctor->id,
                            'title' => $doctor->user->name ?? 'Unknown Doctor',
                            'description' => $doctor->specialization,
                            'url' => "/doctors/{$doctor->id}",
                            'score' => 1.0
                        ];
                    });

                $results['doctors'] = $doctors->toArray();
                $totalResults += $doctors->count();
            }

            // Search appointments
            if (in_array('appointments', $searchTypes)) {
                $appointments = Appointment::where('clinic_id', $clinicId)
                    ->where(function ($q) use ($query) {
                        $q->where('notes', 'like', "%{$query}%")
                          ->orWhere('type', 'like', "%{$query}%")
                          ->orWhereHas('patient', function ($patientQuery) use ($query) {
                              $patientQuery->where('first_name', 'like', "%{$query}%")
                                          ->orWhere('last_name', 'like', "%{$query}%");
                          });
                    })
                    ->with(['patient', 'doctor'])
                    ->limit($limit)
                    ->get()
                    ->map(function ($appointment) {
                        return [
                            'type' => 'appointment',
                            'id' => $appointment->id,
                            'title' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                            'description' => $appointment->type . ' - ' . $appointment->appointment_date,
                            'url' => "/appointments/{$appointment->id}",
                            'score' => 1.0
                        ];
                    });

                $results['appointments'] = $appointments->toArray();
                $totalResults += $appointments->count();
            }

            // Search prescriptions
            if (in_array('prescriptions', $searchTypes)) {
                $prescriptions = Prescription::where('clinic_id', $clinicId)
                    ->where(function ($q) use ($query) {
                        $q->where('notes', 'like', "%{$query}%")
                          ->orWhere('prescription_number', 'like', "%{$query}%")
                          ->orWhereHas('patient', function ($patientQuery) use ($query) {
                              $patientQuery->where('first_name', 'like', "%{$query}%")
                                          ->orWhere('last_name', 'like', "%{$query}%");
                          })
                          ->orWhereHas('items', function ($itemQuery) use ($query) {
                              $itemQuery->where('medication_name', 'like', "%{$query}%");
                          });
                    })
                    ->with(['patient', 'items'])
                    ->limit($limit)
                    ->get()
                    ->map(function ($prescription) {
                        return [
                            'type' => 'prescription',
                            'id' => $prescription->id,
                            'title' => $prescription->patient->first_name . ' ' . $prescription->patient->last_name,
                            'description' => 'Prescription - ' . $prescription->prescription_number,
                            'url' => "/prescriptions/{$prescription->id}",
                            'score' => 1.0
                        ];
                    });

                $results['prescriptions'] = $prescriptions->toArray();
                $totalResults += $prescriptions->count();
            }

            // Search users
            if (in_array('users', $searchTypes)) {
                $users = User::where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->limit($limit)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'type' => 'user',
                            'id' => $user->id,
                            'title' => $user->name,
                            'description' => $user->email,
                            'url' => "/users/{$user->id}",
                            'score' => 1.0
                        ];
                    });

                $results['users'] = $users->toArray();
                $totalResults += $users->count();
            }

            $response = [
                'query' => $query,
                'total_results' => $totalResults,
                'results' => $results
            ];

            return $this->successResponse($response, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to perform global search: ' . $e->getMessage());
        }
    }
}
