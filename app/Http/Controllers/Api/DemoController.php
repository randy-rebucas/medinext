<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Database\Seeders\DemoAccountSeeder;
use App\Models\Clinic;
use App\Models\User;
use Exception;

class DemoController extends Controller
{
    /**
     * Create a demo account with comprehensive sample data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createDemoAccount(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'clinic_name' => 'nullable|string|max:255',
                'admin_email' => 'nullable|email|max:255',
                'admin_password' => 'nullable|string|min:6|max:255',
                'fresh' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if demo clinic already exists
            $existingDemoClinic = Clinic::where('slug', 'demo-medical-center')->first();
            if ($existingDemoClinic && !$request->boolean('fresh')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demo clinic already exists. Use fresh=true to recreate.',
                    'existing_clinic' => [
                        'id' => $existingDemoClinic->id,
                        'name' => $existingDemoClinic->name,
                        'created_at' => $existingDemoClinic->created_at
                    ]
                ], 409);
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Run fresh migration if requested
                if ($request->boolean('fresh')) {
                    Artisan::call('migrate:fresh', ['--force' => true]);
                }

                // Run the demo seeder
                $seeder = new DemoAccountSeeder();
                $seeder->run();

                // Get the created demo clinic and admin user
                $demoClinic = Clinic::where('slug', 'demo-medical-center')->first();
                $demoAdmin = User::where('email', 'demo@medinext.com')->first();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Demo account created successfully',
                    'data' => [
                        'clinic' => [
                            'id' => $demoClinic->id,
                            'name' => $demoClinic->name,
                            'slug' => $demoClinic->slug,
                            'email' => $demoClinic->email,
                            'phone' => $demoClinic->phone,
                            'address' => $demoClinic->address,
                        ],
                        'admin_user' => [
                            'id' => $demoAdmin->id,
                            'name' => $demoAdmin->name,
                            'email' => $demoAdmin->email,
                            'trial_status' => $demoAdmin->getAccessStatus(),
                        ],
                        'demo_data_summary' => $this->getDemoDataSummary($demoClinic->id),
                        'login_credentials' => [
                            'admin' => [
                                'email' => 'demo@medinext.com',
                                'password' => 'demo123'
                            ],
                            'staff' => [
                                'doctors' => [
                                    'doctor1@demomedical.com' => 'demo123',
                                    'doctor2@demomedical.com' => 'demo123',
                                    'doctor3@demomedical.com' => 'demo123',
                                    'doctor4@demomedical.com' => 'demo123',
                                    'doctor5@demomedical.com' => 'demo123',
                                ],
                                'receptionist' => [
                                    'receptionist@demomedical.com' => 'demo123'
                                ]
                            ]
                        ]
                    ]
                ], 201);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create demo account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get demo account information
     *
     * @return JsonResponse
     */
    public function getDemoAccountInfo(): JsonResponse
    {
        try {
            $demoClinic = Clinic::where('slug', 'demo-medical-center')->first();
            
            if (!$demoClinic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demo clinic not found'
                ], 404);
            }

            $demoAdmin = User::where('email', 'demo@medinext.com')->first();
            $demoDoctors = User::where('email', 'like', 'doctor%@demomedical.com')->get();
            $demoReceptionist = User::where('email', 'receptionist@demomedical.com')->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'clinic' => [
                        'id' => $demoClinic->id,
                        'name' => $demoClinic->name,
                        'slug' => $demoClinic->slug,
                        'email' => $demoClinic->email,
                        'phone' => $demoClinic->phone,
                        'address' => $demoClinic->address,
                        'settings' => $demoClinic->settings,
                        'created_at' => $demoClinic->created_at,
                    ],
                    'admin_user' => $demoAdmin ? [
                        'id' => $demoAdmin->id,
                        'name' => $demoAdmin->name,
                        'email' => $demoAdmin->email,
                        'trial_status' => $demoAdmin->getAccessStatus(),
                        'created_at' => $demoAdmin->created_at,
                    ] : null,
                    'staff' => [
                        'doctors' => $demoDoctors->map(function ($doctor) {
                            return [
                                'id' => $doctor->id,
                                'name' => $doctor->name,
                                'email' => $doctor->email,
                                'created_at' => $doctor->created_at,
                            ];
                        }),
                        'receptionist' => $demoReceptionist ? [
                            'id' => $demoReceptionist->id,
                            'name' => $demoReceptionist->name,
                            'email' => $demoReceptionist->email,
                            'created_at' => $demoReceptionist->created_at,
                        ] : null,
                    ],
                    'demo_data_summary' => $this->getDemoDataSummary($demoClinic->id),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get demo account info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete demo account and all associated data
     *
     * @return JsonResponse
     */
    public function deleteDemoAccount(): JsonResponse
    {
        try {
            $demoClinic = Clinic::where('slug', 'demo-medical-center')->first();
            
            if (!$demoClinic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demo clinic not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Delete all demo users
                User::where('email', 'like', '%@demomedical.com')
                    ->orWhere('email', 'demo@medinext.com')
                    ->delete();

                // Delete demo clinic (this will cascade delete related data)
                $demoClinic->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Demo account deleted successfully'
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete demo account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset demo account data (keep clinic and users, reset all other data)
     *
     * @return JsonResponse
     */
    public function resetDemoData(): JsonResponse
    {
        try {
            $demoClinic = Clinic::where('slug', 'demo-medical-center')->first();
            
            if (!$demoClinic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demo clinic not found'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Delete all demo data except clinic and users
                $this->deleteDemoData($demoClinic->id);

                // Recreate demo data
                $seeder = new DemoAccountSeeder();
                $seeder->run();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Demo data reset successfully',
                    'data' => [
                        'demo_data_summary' => $this->getDemoDataSummary($demoClinic->id)
                    ]
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset demo data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary of demo data
     *
     * @param int $clinicId
     * @return array
     */
    private function getDemoDataSummary(int $clinicId): array
    {
        // Get patient IDs for this clinic
        $patientIds = DB::table('patients')->where('clinic_id', $clinicId)->pluck('id');
        
        // Get user IDs for this clinic
        $userIds = DB::table('user_clinic_roles')->where('clinic_id', $clinicId)->pluck('user_id');
        
        return [
            'patients' => DB::table('patients')->where('clinic_id', $clinicId)->count(),
            'doctors' => DB::table('doctors')->where('clinic_id', $clinicId)->count(),
            'appointments' => DB::table('appointments')->where('clinic_id', $clinicId)->count(),
            'encounters' => DB::table('encounters')->where('clinic_id', $clinicId)->count(),
            'prescriptions' => DB::table('prescriptions')->where('clinic_id', $clinicId)->count(),
            'lab_results' => DB::table('lab_results')->where('clinic_id', $clinicId)->count(),
            'bills' => DB::table('bills')->where('clinic_id', $clinicId)->count(),
            'insurance_records' => DB::table('insurance')->whereIn('patient_id', $patientIds)->count(),
            'activity_logs' => DB::table('activity_logs')->where('clinic_id', $clinicId)->count(),
            'notifications' => DB::table('notifications')->whereIn('user_id', $userIds)->count(),
            'rooms' => DB::table('rooms')->where('clinic_id', $clinicId)->count(),
        ];
    }

    /**
     * Delete demo data (except clinic and users)
     *
     * @param int $clinicId
     * @return void
     */
    private function deleteDemoData(int $clinicId): void
    {
        // Delete in reverse order of dependencies
        DB::table('activity_logs')->where('clinic_id', $clinicId)->delete();
        DB::table('notifications')->whereIn('user_id', function($query) use ($clinicId) {
            $query->select('user_id')->from('user_clinic_roles')->where('clinic_id', $clinicId);
        })->delete();
        DB::table('queue_patients')->whereIn('queue_id', function($query) use ($clinicId) {
            $query->select('id')->from('queues')->where('clinic_id', $clinicId);
        })->delete();
        DB::table('queues')->where('clinic_id', $clinicId)->delete();
        DB::table('insurance')->whereIn('patient_id', function($query) use ($clinicId) {
            $query->select('id')->from('patients')->where('clinic_id', $clinicId);
        })->delete();
        DB::table('bill_items')->whereIn('bill_id', function($query) use ($clinicId) {
            $query->select('id')->from('bills')->where('clinic_id', $clinicId);
        })->delete();
        DB::table('bills')->where('clinic_id', $clinicId)->delete();
        DB::table('lab_results')->where('clinic_id', $clinicId)->delete();
        DB::table('prescriptions')->where('clinic_id', $clinicId)->delete();
        DB::table('encounters')->where('clinic_id', $clinicId)->delete();
        DB::table('appointments')->where('clinic_id', $clinicId)->delete();
        DB::table('patients')->where('clinic_id', $clinicId)->delete();
        DB::table('rooms')->where('clinic_id', $clinicId)->delete();
        DB::table('doctors')->where('clinic_id', $clinicId)->delete();
        DB::table('user_clinic_roles')->where('clinic_id', $clinicId)->delete();
    }
}
