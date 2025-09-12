<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Insurance;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Insurance",
 *     type="object",
 *     title="Insurance",
 *     description="Insurance model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="provider_name", type="string", example="Blue Cross Blue Shield"),
 *     @OA\Property(property="policy_number", type="string", example="BC123456789"),
 *     @OA\Property(property="group_number", type="string", example="GRP001"),
 *     @OA\Property(property="coverage_type", type="string", enum={"primary", "secondary", "tertiary"}, example="primary"),
 *     @OA\Property(property="coverage_percentage", type="number", format="float", example=80.00),
 *     @OA\Property(property="deductible", type="number", format="float", example=500.00),
 *     @OA\Property(property="copay", type="number", format="float", example=25.00),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="expiry_date", type="string", format="date", example="2024-12-31"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */



class InsuranceController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/insurance",
     *     summary="Get all insurance records",
     *     description="Retrieve a paginated list of insurance records with optional filtering",
     *     operationId="getInsuranceRecords",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="patient_id",
     *         in="query",
     *         description="Filter by patient ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="provider_name",
     *         in="query",
     *         description="Filter by insurance provider",
     *         required=false,
     *         @OA\Schema(type="string", example="Blue Cross")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance records retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance records retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="pagination", ref="#/components/schemas/Pagination")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Insurance::with(['patient']);

            // Apply filters
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            if ($request->has('provider_name')) {
                $query->where('provider_name', 'like', '%' . $request->get('provider_name') . '%');
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->get('is_active'));
            }

            $perPage = $request->get('per_page', 15);
            $insurance = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse($insurance, 'Insurance records retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve insurance records: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/insurance",
     *     summary="Create a new insurance record",
     *     description="Create a new insurance record",
     *     operationId="createInsuranceRecord",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id", "provider_name", "policy_number"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="provider_name", type="string", example="Blue Cross Blue Shield"),
     *             @OA\Property(property="policy_number", type="string", example="BC123456789"),
     *             @OA\Property(property="group_number", type="string", example="GRP001"),
     *             @OA\Property(property="coverage_type", type="string", enum={"primary", "secondary", "tertiary"}, example="primary"),
     *             @OA\Property(property="coverage_percentage", type="number", format="float", example=80.00),
     *             @OA\Property(property="deductible", type="number", format="float", example=500.00),
     *             @OA\Property(property="copay", type="number", format="float", example=25.00),
     *             @OA\Property(property="expiry_date", type="string", format="date", example="2024-12-31")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Insurance record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance record created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|exists:patients,id',
                'provider_name' => 'required|string|max:255',
                'policy_number' => 'required|string|max:100|unique:insurance,policy_number',
                'group_number' => 'nullable|string|max:100',
                'coverage_type' => 'nullable|in:primary,secondary,tertiary',
                'coverage_percentage' => 'nullable|numeric|min:0|max:100',
                'deductible' => 'nullable|numeric|min:0',
                'copay' => 'nullable|numeric|min:0',
                'expiry_date' => 'nullable|date|after:today'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $insuranceData = $request->all();
            $insuranceData['is_active'] = true;

            $insurance = Insurance::create($insuranceData);
            $insurance->load(['patient']);

            return $this->successResponse($insurance, 'Insurance record created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create insurance record: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/insurance/{id}",
     *     summary="Get a specific insurance record",
     *     description="Retrieve a specific insurance record by ID",
     *     operationId="getInsuranceRecord",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Insurance ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance record retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance record retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Insurance record not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $insurance = Insurance::with(['patient'])->findOrFail($id);
            return $this->successResponse($insurance, 'Insurance record retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Insurance record not found', null, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/insurance/{id}",
     *     summary="Update an insurance record",
     *     description="Update an existing insurance record",
     *     operationId="updateInsuranceRecord",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Insurance ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="provider_name", type="string", example="Blue Cross Blue Shield"),
     *             @OA\Property(property="policy_number", type="string", example="BC123456789"),
     *             @OA\Property(property="group_number", type="string", example="GRP001"),
     *             @OA\Property(property="coverage_type", type="string", enum={"primary", "secondary", "tertiary"}, example="primary"),
     *             @OA\Property(property="coverage_percentage", type="number", format="float", example=80.00),
     *             @OA\Property(property="deductible", type="number", format="float", example=500.00),
     *             @OA\Property(property="copay", type="number", format="float", example=25.00),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="expiry_date", type="string", format="date", example="2024-12-31")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance record updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Insurance record not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $insurance = Insurance::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'provider_name' => 'sometimes|required|string|max:255',
                'policy_number' => 'sometimes|required|string|max:100|unique:insurance,policy_number,' . $id,
                'group_number' => 'nullable|string|max:100',
                'coverage_type' => 'nullable|in:primary,secondary,tertiary',
                'coverage_percentage' => 'nullable|numeric|min:0|max:100',
                'deductible' => 'nullable|numeric|min:0',
                'copay' => 'nullable|numeric|min:0',
                'is_active' => 'sometimes|boolean',
                'expiry_date' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $insurance->update($request->all());
            $insurance->load(['patient']);

            return $this->successResponse($insurance, 'Insurance record updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update insurance record: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/insurance/{id}",
     *     summary="Delete an insurance record",
     *     description="Delete an insurance record (soft delete)",
     *     operationId="deleteInsuranceRecord",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Insurance ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance record deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Insurance record not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $insurance = Insurance::findOrFail($id);
            $insurance->delete();

            return $this->successResponse(null, 'Insurance record deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete insurance record: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/insurance/{id}/verify",
     *     summary="Verify insurance coverage",
     *     description="Verify insurance coverage and eligibility",
     *     operationId="verifyInsurance",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Insurance ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Insurance verification completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance verification completed"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_valid", type="boolean", example=true),
     *                 @OA\Property(property="coverage_status", type="string", example="active"),
     *                 @OA\Property(property="remaining_deductible", type="number", format="float", example=250.00),
     *                 @OA\Property(property="verification_date", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Insurance record not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function verify($id): JsonResponse
    {
        try {
            $insurance = Insurance::findOrFail($id);

            // Implementation for insurance verification would go here
            // This is a placeholder response
            $verificationResult = [
                'is_valid' => $insurance->is_active,
                'coverage_status' => $insurance->is_active ? 'active' : 'inactive',
                'remaining_deductible' => $insurance->deductible,
                'verification_date' => now()->toISOString()
            ];

            return $this->successResponse($verificationResult, 'Insurance verification completed');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to verify insurance: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/insurance/providers",
     *     summary="Get insurance providers",
     *     description="Retrieve a list of available insurance providers",
     *     operationId="getInsuranceProviders",
     *     tags={"Insurance"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Insurance providers retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Insurance providers retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Blue Cross Blue Shield"),
     *                     @OA\Property(property="code", type="string", example="BCBS"),
     *                     @OA\Property(property="is_active", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function providers(): JsonResponse
    {
        try {
            // Implementation for getting insurance providers would go here
            // This is a placeholder response
            $providers = [
                ['name' => 'Blue Cross Blue Shield', 'code' => 'BCBS', 'is_active' => true],
                ['name' => 'Aetna', 'code' => 'AETNA', 'is_active' => true],
                ['name' => 'Cigna', 'code' => 'CIGNA', 'is_active' => true],
                ['name' => 'UnitedHealth', 'code' => 'UHC', 'is_active' => true]
            ];

            return $this->successResponse($providers, 'Insurance providers retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve insurance providers: ' . $e->getMessage());
        }
    }
}
