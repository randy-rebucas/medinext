<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Bill;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="Bill",
 *     type="object",
 *     title="Bill",
 *     description="Bill model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="clinic_id", type="integer", example=1),
 *     @OA\Property(property="bill_number", type="string", example="B001"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=150.00),
 *     @OA\Property(property="paid_amount", type="number", format="float", example=0.00),
 *     @OA\Property(property="status", type="string", enum={"pending", "paid", "partial", "cancelled"}, example="pending"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2024-01-31"),
 *     @OA\Property(property="notes", type="string", example="Consultation fee"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */



class BillController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/bills",
     *     summary="Get all bills",
     *     description="Retrieve a paginated list of bills with optional filtering",
     *     operationId="getBills",
     *     tags={"Billing"},
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
     *         name="status",
     *         in="query",
     *         description="Filter by bill status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "paid", "partial", "cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bills retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bills retrieved successfully"),
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
            // Permission check is handled by middleware, but we can add additional validation
            $this->requirePermission('billing.view');

            $currentClinic = $this->getCurrentClinic();
            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $query = Bill::with(['patient', 'clinic'])
                ->where('clinic_id', $currentClinic->id);

            // Apply filters
            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            $perPage = $request->get('per_page', 15);
            $bills = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->successResponse($bills, 'Bills retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve bills: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bills",
     *     summary="Create a new bill",
     *     description="Create a new bill",
     *     operationId="createBill",
     *     tags={"Billing"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"patient_id", "total_amount"},
     *             @OA\Property(property="patient_id", type="integer", example=1),
     *             @OA\Property(property="total_amount", type="number", format="float", example=150.00),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-01-31"),
     *             @OA\Property(property="notes", type="string", example="Consultation fee")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bill created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bill created successfully"),
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
                'total_amount' => 'required|numeric|min:0',
                'due_date' => 'nullable|date|after:today',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $billData = $request->all();
            $billData['clinic_id'] = Auth::user()->current_clinic_id ?? 1;
            $billData['bill_number'] = $this->generateBillNumber();
            $billData['status'] = 'pending';
            $billData['paid_amount'] = 0.00;

            $bill = Bill::create($billData);
            $bill->load(['patient', 'clinic']);

            return $this->successResponse($bill, 'Bill created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create bill: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bills/{id}",
     *     summary="Get a specific bill",
     *     description="Retrieve a specific bill by ID",
     *     operationId="getBill",
     *     tags={"Billing"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Bill ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bill retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $bill = Bill::with(['patient', 'clinic'])->findOrFail($id);
            return $this->successResponse($bill, 'Bill retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Bill not found', null, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/bills/{id}",
     *     summary="Update a bill",
     *     description="Update an existing bill",
     *     operationId="updateBill",
     *     tags={"Billing"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Bill ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="total_amount", type="number", format="float", example=150.00),
     *             @OA\Property(property="due_date", type="string", format="date", example="2024-01-31"),
     *             @OA\Property(property="notes", type="string", example="Consultation fee"),
     *             @OA\Property(property="status", type="string", enum={"pending", "paid", "partial", "cancelled"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bill updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found",
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
            $bill = Bill::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'total_amount' => 'sometimes|required|numeric|min:0',
                'due_date' => 'nullable|date',
                'notes' => 'nullable|string|max:500',
                'status' => 'sometimes|in:pending,paid,partial,cancelled'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $bill->update($request->all());
            $bill->load(['patient', 'clinic']);

            return $this->successResponse($bill, 'Bill updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update bill: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/bills/{id}",
     *     summary="Delete a bill",
     *     description="Delete a bill (soft delete)",
     *     operationId="deleteBill",
     *     tags={"Billing"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Bill ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bill deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $bill = Bill::findOrFail($id);
            $bill->delete();

            return $this->successResponse(null, 'Bill deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete bill: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bills/{id}/pay",
     *     summary="Process payment for bill",
     *     description="Process a payment for a bill",
     *     operationId="payBill",
     *     tags={"Billing"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Bill ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "payment_method"},
     *             @OA\Property(property="amount", type="number", format="float", example=150.00),
     *             @OA\Property(property="payment_method", type="string", enum={"cash", "card", "insurance", "bank_transfer"}, example="card"),
     *             @OA\Property(property="transaction_id", type="string", example="TXN123456"),
     *             @OA\Property(property="notes", type="string", example="Payment processed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment processed successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function pay(Request $request, $id): JsonResponse
    {
        try {
            $bill = Bill::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,card,insurance,bank_transfer',
                'transaction_id' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $paymentAmount = $request->get('amount');
            $newPaidAmount = $bill->paid_amount + $paymentAmount;

            // Update bill status based on payment
            if ($newPaidAmount >= $bill->total_amount) {
                $bill->status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $bill->status = 'partial';
            }

            $bill->paid_amount = $newPaidAmount;
            $bill->save();

            return $this->successResponse($bill, 'Payment processed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bills/outstanding",
     *     summary="Get outstanding bills",
     *     description="Retrieve all outstanding (unpaid) bills",
     *     operationId="getOutstandingBills",
     *     tags={"Billing"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Outstanding bills retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Outstanding bills retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function outstanding(): JsonResponse
    {
        try {
            $bills = Bill::with(['patient', 'clinic'])
                ->whereIn('status', ['pending', 'partial'])
                ->orderBy('due_date', 'asc')
                ->get();

            return $this->successResponse($bills, 'Outstanding bills retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve outstanding bills: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique bill number
     */
    private function generateBillNumber(): string
    {
        $lastBill = Bill::orderBy('id', 'desc')->first();
        $nextNumber = $lastBill ? $lastBill->id + 1 : 1;
        return 'B' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
