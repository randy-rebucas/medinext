<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Insurance;
use App\Models\Patient;
use Illuminate\Support\Facades\Validator;

class InsuranceController extends Controller
{
    /**
     * Display the insurance management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Insurance Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/insurance', [
                'insurances' => [],
                'patients' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get insurances for this clinic
        $insurances = $this->getInsurances($clinicId);

        // Get patients for dropdown
        $patients = $this->getPatients($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/insurance', [
            'insurances' => $insurances,
            'patients' => $patients,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created insurance
     */
    public function store(Request $request)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'provider_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.&]+$/',
            'policy_number' => 'required|string|max:255|regex:/^[a-zA-Z0-9\-\s]+$/',
            'group_number' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\-\s]+$/',
            'coverage_type' => 'required|string|in:primary,secondary,tertiary',
            'coverage_percentage' => 'required|numeric|min:0|max:100',
            'deductible' => 'nullable|numeric|min:0|max:999999.99',
            'copay' => 'nullable|numeric|min:0|max:999999.99',
            'is_active' => 'nullable|boolean',
            'expiry_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'provider_name.regex' => 'Provider name can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and ampersands.',
            'policy_number.regex' => 'Policy number can only contain letters, numbers, hyphens, and spaces.',
            'group_number.regex' => 'Group number can only contain letters, numbers, hyphens, and spaces.',
            'coverage_type.in' => 'Invalid coverage type.',
            'coverage_percentage.min' => 'Coverage percentage must be at least 0%.',
            'coverage_percentage.max' => 'Coverage percentage cannot exceed 100%.',
            'deductible.max' => 'Deductible cannot exceed 999,999.99.',
            'copay.max' => 'Copay cannot exceed 999,999.99.',
            'expiry_date.after' => 'Expiry date must be in the future.',
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
            $this->logWebRequest('Create Insurance', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated insurance creation attempt');
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

            // Create insurance
            $insurance = Insurance::create([
                'clinic_id' => $clinicId,
                'patient_id' => $request->patient_id,
                'provider_name' => $request->provider_name,
                'policy_number' => $request->policy_number,
                'group_number' => $request->group_number,
                'coverage_type' => $request->coverage_type,
                'coverage_percentage' => $request->coverage_percentage,
                'deductible' => $request->deductible,
                'copay' => $request->copay,
                'is_active' => $request->is_active ?? true,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Insurance created successfully',
                'insurance' => $this->getInsurance($insurance->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'InsuranceController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create insurance. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified insurance
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'provider_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\'\.&]+$/',
            'policy_number' => 'required|string|max:255|regex:/^[a-zA-Z0-9\-\s]+$/',
            'group_number' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\-\s]+$/',
            'coverage_type' => 'required|string|in:primary,secondary,tertiary',
            'coverage_percentage' => 'required|numeric|min:0|max:100',
            'deductible' => 'nullable|numeric|min:0|max:999999.99',
            'copay' => 'nullable|numeric|min:0|max:999999.99',
            'is_active' => 'nullable|boolean',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'provider_name.regex' => 'Provider name can only contain letters, numbers, spaces, hyphens, apostrophes, periods, and ampersands.',
            'policy_number.regex' => 'Policy number can only contain letters, numbers, hyphens, and spaces.',
            'group_number.regex' => 'Group number can only contain letters, numbers, hyphens, and spaces.',
            'coverage_type.in' => 'Invalid coverage type.',
            'coverage_percentage.min' => 'Coverage percentage must be at least 0%.',
            'coverage_percentage.max' => 'Coverage percentage cannot exceed 100%.',
            'deductible.max' => 'Deductible cannot exceed 999,999.99.',
            'copay.max' => 'Copay cannot exceed 999,999.99.',
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
            $insurance = Insurance::findOrFail($id);

            // Update insurance
            $insurance->update([
                'patient_id' => $request->patient_id,
                'provider_name' => $request->provider_name,
                'policy_number' => $request->policy_number,
                'group_number' => $request->group_number,
                'coverage_type' => $request->coverage_type,
                'coverage_percentage' => $request->coverage_percentage,
                'deductible' => $request->deductible,
                'copay' => $request->copay,
                'is_active' => $request->is_active ?? true,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Insurance updated successfully',
                'insurance' => $this->getInsurance($insurance->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'InsuranceController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update insurance. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified insurance
     */
    public function destroy($id)
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $insurance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Insurance deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'InsuranceController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete insurance. Please try again.'
            ], 500);
        }
    }

    /**
     * Get insurance details
     */
    public function show($id)
    {
        try {
            $insurance = $this->getInsurance($id);

            if (!$insurance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Insurance retrieved successfully',
                'insurance' => $insurance
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'InsuranceController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve insurance. Please try again.'
            ], 500);
        }
    }

    /**
     * Get insurances for a clinic with caching
     */
    private function getInsurances($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('insurances', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return Insurance::where('clinic_id', $clinicId)
                ->with(['patient:id,first_name,last_name'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($insurance) {
                    return [
                        'id' => $insurance->id,
                        'provider_name' => $insurance->provider_name,
                        'policy_number' => $insurance->policy_number,
                        'group_number' => $insurance->group_number,
                        'coverage_type' => $insurance->coverage_type,
                        'coverage_percentage' => $insurance->coverage_percentage,
                        'deductible' => $insurance->deductible,
                        'copay' => $insurance->copay,
                        'is_active' => $insurance->is_active,
                        'expiry_date' => $insurance->expiry_date,
                        'notes' => $insurance->notes,
                        'patient_name' => $insurance->patient->first_name . ' ' . $insurance->patient->last_name,
                        'patient_id' => $insurance->patient_id,
                        'status' => $insurance->is_active ? 'Active' : 'Inactive',
                        'created_at' => $insurance->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $insurance->updated_at->format('Y-m-d H:i:s'),
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
     * Get a single insurance
     */
    private function getInsurance($insuranceId)
    {
        $insurance = Insurance::with(['patient'])->findOrFail($insuranceId);

        return [
            'id' => $insurance->id,
            'provider_name' => $insurance->provider_name,
            'policy_number' => $insurance->policy_number,
            'group_number' => $insurance->group_number,
            'coverage_type' => $insurance->coverage_type,
            'coverage_percentage' => $insurance->coverage_percentage,
            'deductible' => $insurance->deductible,
            'copay' => $insurance->copay,
            'is_active' => $insurance->is_active,
            'expiry_date' => $insurance->expiry_date,
            'notes' => $insurance->notes,
            'patient' => [
                'id' => $insurance->patient->id,
                'name' => $insurance->patient->first_name . ' ' . $insurance->patient->last_name,
            ],
            'status' => $insurance->is_active ? 'Active' : 'Inactive',
            'created_at' => $insurance->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $insurance->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_insurance', 'view_insurance', 'create_insurance', 'edit_insurance', 'delete_insurance'
            ],
            'admin' => [
                'manage_insurance', 'view_insurance', 'create_insurance', 'edit_insurance', 'delete_insurance'
            ],
            'doctor' => [
                'view_insurance', 'create_insurance', 'edit_insurance'
            ],
            'receptionist' => [
                'view_insurance', 'create_insurance', 'edit_insurance'
            ],
            'patient' => [
                'view_insurance'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
