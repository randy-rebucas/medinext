<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Bill;
use App\Models\Patient;
use App\Models\Clinic;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BillController extends Controller
{
    /**
     * Display the billing management page
     */
    public function index(Request $request): Response
    {
        $this->logWebRequest('Billing Management Access', ['action' => 'index']);
        
        $user = $request->user();
        $userClinicRole = $this->getUserClinicRole($request);

        if (!$userClinicRole) {
            return Inertia::render('admin/bills', [
                'bills' => [],
                'patients' => [],
                'permissions' => [],
            ]);
        }

        $clinicId = $userClinicRole->clinic_id;

        // Get bills for this clinic
        $bills = $this->getBills($clinicId);

        // Get patients for dropdown
        $patients = $this->getPatients($clinicId);

        // Get user permissions
        $permissions = $this->getUserPermissions($userClinicRole->role->name ?? 'user');

        return Inertia::render('admin/bills', [
            'bills' => $bills,
            'patients' => $patients,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created bill
     */
    public function store(Request $request)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'bill_number' => 'required|string|max:255|unique:bills,bill_number',
            'total_amount' => 'required|numeric|min:0|max:999999.99',
            'paid_amount' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|string|in:pending,paid,partial,cancelled',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0|max:999999.99',
            'items.*.total' => 'required|numeric|min:0|max:999999.99',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'bill_number.unique' => 'Bill number already exists.',
            'total_amount.max' => 'Total amount cannot exceed 999,999.99.',
            'paid_amount.max' => 'Paid amount cannot exceed 999,999.99.',
            'due_date.after' => 'Due date must be in the future.',
            'items.required' => 'At least one bill item is required.',
            'items.*.unit_price.max' => 'Unit price cannot exceed 999,999.99.',
            'items.*.total.max' => 'Item total cannot exceed 999,999.99.',
        ];

        try {
            $validatedData = $this->validateAndSanitize($request, $rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $this->logWebRequest('Create Bill', ['action' => 'store']);
            
            $user = $request->user();

            if (!$user) {
                $this->logSecurityEvent('Unauthenticated bill creation attempt');
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

            // Create bill
            $bill = Bill::create([
                'clinic_id' => $clinicId,
                'patient_id' => $validatedData['patient_id'],
                'bill_number' => $validatedData['bill_number'],
                'total_amount' => $validatedData['total_amount'],
                'paid_amount' => $validatedData['paid_amount'] ?? 0,
                'status' => $validatedData['status'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            // Create bill items
            foreach ($validatedData['items'] as $itemData) {
                $bill->items()->create($itemData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bill created successfully',
                'bill' => $this->getBill($bill->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'BillController::store');
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bill. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified bill
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'bill_number' => 'required|string|max:255|unique:bills,bill_number,' . $id,
            'total_amount' => 'required|numeric|min:0|max:999999.99',
            'paid_amount' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|string|in:pending,paid,partial,cancelled',
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];

        $messages = [
            'patient_id.exists' => 'Selected patient does not exist.',
            'bill_number.unique' => 'Bill number already exists.',
            'total_amount.max' => 'Total amount cannot exceed 999,999.99.',
            'paid_amount.max' => 'Paid amount cannot exceed 999,999.99.',
        ];

        try {
            $validatedData = $this->validateAndSanitize($request, $rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $bill = Bill::findOrFail($id);

            // Update bill
            $bill->update([
                'patient_id' => $validatedData['patient_id'],
                'bill_number' => $validatedData['bill_number'],
                'total_amount' => $validatedData['total_amount'],
                'paid_amount' => $validatedData['paid_amount'] ?? 0,
                'status' => $validatedData['status'],
                'due_date' => $validatedData['due_date'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bill updated successfully',
                'bill' => $this->getBill($bill->id)
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'BillController::update');
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bill. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified bill
     */
    public function destroy($id)
    {
        try {
            $bill = Bill::findOrFail($id);
            $bill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'BillController::destroy');
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bill. Please try again.'
            ], 500);
        }
    }

    /**
     * Get bill details
     */
    public function show($id)
    {
        try {
            $bill = $this->getBill($id);

            if (!$bill) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bill retrieved successfully',
                'bill' => $bill
            ]);

        } catch (\Exception $e) {
            $this->handleException($e, 'BillController::show');
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve bill. Please try again.'
            ], 500);
        }
    }

    /**
     * Get bills for a clinic with caching
     */
    private function getBills($clinicId)
    {
        $cacheKey = $this->getClinicCacheKey('bills', $clinicId);
        
        return $this->remember($cacheKey, 30, function () use ($clinicId) {
            return Bill::where('clinic_id', $clinicId)
                ->with(['patient:id,first_name,last_name', 'items'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($bill) {
                    return [
                        'id' => $bill->id,
                        'bill_number' => $bill->bill_number,
                        'patient_name' => $bill->patient->first_name . ' ' . $bill->patient->last_name,
                        'patient_id' => $bill->patient_id,
                        'total_amount' => $bill->total_amount,
                        'paid_amount' => $bill->paid_amount,
                        'balance' => $bill->total_amount - $bill->paid_amount,
                        'status' => $bill->status,
                        'due_date' => $bill->due_date,
                        'notes' => $bill->notes,
                        'items_count' => $bill->items->count(),
                        'created_at' => $bill->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $bill->updated_at->format('Y-m-d H:i:s'),
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
     * Get a single bill
     */
    private function getBill($billId)
    {
        $bill = Bill::with(['patient', 'items'])->findOrFail($billId);

        return [
            'id' => $bill->id,
            'bill_number' => $bill->bill_number,
            'patient' => [
                'id' => $bill->patient->id,
                'name' => $bill->patient->first_name . ' ' . $bill->patient->last_name,
            ],
            'total_amount' => $bill->total_amount,
            'paid_amount' => $bill->paid_amount,
            'balance' => $bill->total_amount - $bill->paid_amount,
            'status' => $bill->status,
            'due_date' => $bill->due_date,
            'notes' => $bill->notes,
            'items' => $bill->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ];
            }),
            'created_at' => $bill->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $bill->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($role)
    {
        $permissions = [
            'superadmin' => [
                'manage_billing', 'view_billing', 'create_billing', 'edit_billing', 'delete_billing',
                'view_financial_reports', 'export_financial_reports'
            ],
            'admin' => [
                'manage_billing', 'view_billing', 'create_billing', 'edit_billing', 'delete_billing',
                'view_financial_reports', 'export_financial_reports'
            ],
            'doctor' => [
                'view_billing', 'create_billing'
            ],
            'receptionist' => [
                'view_billing', 'create_billing', 'edit_billing'
            ],
            'patient' => [
                'view_billing'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
