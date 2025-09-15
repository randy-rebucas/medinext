<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserClinicRole;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Show the welcome step of onboarding
     */
    public function welcome(): Response
    {
        $this->logWebRequest('Onboarding Welcome', ['action' => 'welcome']);
        
        $user = Auth::user();
        if (!$user) {
            $this->logSecurityEvent('Unauthenticated onboarding access attempt');
            return redirect()->route('login');
        }

        // Ensure user has trial started
        if (!$user->trial_started_at) {
            $user->startTrial();
        }

        // Ensure user has admin role in their clinic
        $clinic = $user->clinics->first();
        if ($clinic) {
            $this->ensureUserHasAdminRole($user, $clinic);
        }

        return Inertia::render('onboarding/welcome', [
            'user' => $user,
            'trial_status' => $this->getTrialStatus($user),
            'clinic' => $clinic,
        ]);
    }

    /**
     * Show the license activation step
     */
    public function license(): Response
    {
        $user = Auth::user();
        if (!$user) {
            $this->logSecurityEvent('Unauthenticated license page access attempt');
            return redirect()->route('login');
        }

        // Ensure user has trial started
        if (!$user->trial_started_at) {
            $user->startTrial();
        }

        try {
            $licenseInfo = $this->licenseService->getLicenseInfo();
        } catch (\Exception $e) {
            $this->logWebRequest('License info retrieval failed', ['error' => $e->getMessage()]);
            $licenseInfo = [
                'total_licenses' => 0,
                'active_licenses' => 0,
                'available_licenses' => 0,
            ];
        }

        return Inertia::render('onboarding/license', [
            'user' => $user,
            'trial_status' => $this->getTrialStatus($user),
            'license_info' => $licenseInfo,
        ]);
    }

    /**
     * Handle license activation
     */
    public function activateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|max:255|min:10',
        ], [
            'license_key.required' => 'License key is required.',
            'license_key.min' => 'License key must be at least 10 characters.',
            'license_key.max' => 'License key cannot exceed 255 characters.',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'User not authenticated']);
        }

        $licenseKey = trim($request->license_key);

        try {
            // Validate and activate the license
            $result = $this->licenseService->activateLicenseForUser($user, $licenseKey);

            if (!$result['success']) {
                return back()->withErrors([
                    'license_key' => $result['message'] ?? 'Invalid license key. Please check and try again.'
                ])->withInput();
            }

            // Update user's license status
            $user->update([
                'license_key' => $licenseKey,
                'has_activated_license' => true,
                'is_trial_user' => false,
            ]);

            return redirect()->route('onboarding.complete')
                ->with('success', 'License activated successfully! You now have full access to all features.');
        } catch (\Exception $e) {
            \Log::error('License activation failed', [
                'user_id' => $user->id,
                'license_key' => $licenseKey,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'license_key' => 'License activation failed. Please try again or contact support.'
            ])->withInput();
        }
    }

    /**
     * Show the clinic setup step
     */
    public function clinicSetup(): Response
    {
        $user = Auth::user();
        if (!$user) {
            $this->logSecurityEvent('Unauthenticated clinic setup access attempt');
            return redirect()->route('login');
        }

        // Ensure user has trial started
        if (!$user->trial_started_at) {
            $user->startTrial();
        }

        $clinic = $user->clinics->first();

        return Inertia::render('onboarding/clinic-setup', [
            'user' => $user,
            'clinic' => $clinic,
        ]);
    }

    /**
     * Handle clinic setup
     */
    public function updateClinic(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:2',
            'address' => 'required|string|max:500|min:5',
            'city' => 'required|string|max:100|min:2',
            'state' => 'required|string|max:100|min:2',
            'postal_code' => 'required|string|max:20|min:3',
            'country' => 'required|string|max:100|min:2',
            'phone' => 'required|string|max:20|min:10',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Clinic name is required.',
            'name.min' => 'Clinic name must be at least 2 characters.',
            'address.required' => 'Address is required.',
            'address.min' => 'Address must be at least 5 characters.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postal_code.required' => 'Postal code is required.',
            'country.required' => 'Country is required.',
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 10 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'website.url' => 'Please enter a valid website URL.',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'User not authenticated']);
        }

        try {
            $clinic = $user->clinics->first();

            // Create clinic if it doesn't exist
            if (!$clinic) {
                $clinic = $this->createDefaultClinic($user, $request);
            } else {
                // Update existing clinic
                $clinic->update([
                    'name' => trim($request->name),
                    'slug' => \Illuminate\Support\Str::slug($request->name),
                    'timezone' => $request->timezone ?? 'Asia/Manila',
                    'address' => [
                        'street' => trim($request->address),
                        'city' => trim($request->city),
                        'state' => trim($request->state),
                        'postal_code' => trim($request->postal_code),
                        'country' => trim($request->country),
                    ],
                    'phone' => trim($request->phone),
                    'email' => trim($request->email),
                    'website' => $request->website ? trim($request->website) : null,
                    'description' => $request->description ? trim($request->description) : null,
                    'settings' => $this->getDefaultClinicSettings(),
                ]);

                // Ensure user has admin role with proper permissions
                $this->ensureUserHasAdminRole($user, $clinic);
            }

            return redirect()->route('onboarding.team-setup')
                ->with('success', 'Clinic information updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Clinic setup failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return back()->withErrors([
                'error' => 'Failed to update clinic information. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Show the team setup step
     */
    public function teamSetup(): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $clinic = $user->clinics->first();
        if (!$clinic) {
            // Redirect to clinic setup if no clinic exists
            return redirect()->route('onboarding.clinic-setup')
                ->withErrors(['error' => 'Please complete clinic setup first.']);
        }

        $roles = Role::where('is_system_role', false)->get();

        return Inertia::render('onboarding/team-setup', [
            'user' => $user,
            'clinic' => $clinic,
            'roles' => $roles,
        ]);
    }

    /**
     * Handle team setup
     */
    public function updateTeam(Request $request)
    {
        $request->validate([
            'team_members' => 'array',
            'team_members.*.name' => 'required|string|max:255|min:2',
            'team_members.*.email' => 'required|email|max:255',
            'team_members.*.role_id' => 'required|exists:roles,id',
            'team_members.*.department' => 'nullable|string|max:255',
        ], [
            'team_members.*.name.required' => 'Team member name is required.',
            'team_members.*.name.min' => 'Team member name must be at least 2 characters.',
            'team_members.*.email.required' => 'Team member email is required.',
            'team_members.*.email.email' => 'Please enter a valid email address.',
            'team_members.*.role_id.required' => 'Please select a role for the team member.',
            'team_members.*.role_id.exists' => 'Selected role is invalid.',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'User not authenticated']);
        }

        try {
            // For now, just log the team members - actual invitation system can be implemented later
            $teamMembers = $request->team_members;
            
            \Log::info('Team setup completed', [
                'user_id' => $user->id,
                'team_members_count' => count($teamMembers),
                'team_members' => $teamMembers
            ]);

            return redirect()->route('onboarding.complete')
                ->with('success', 'Team setup completed successfully!');
        } catch (\Exception $e) {
            \Log::error('Team setup failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return back()->withErrors([
                'error' => 'Failed to set up team. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Show the completion step
     */
    public function complete(): Response
    {
        $user = Auth::user();
        if (!$user) {
            $this->logSecurityEvent('Unauthenticated completion page access attempt');
            return redirect()->route('login');
        }

        $clinic = $user->clinics->first();
        
        if (!$clinic) {
            // Redirect to clinic setup if no clinic exists
            return redirect()->route('onboarding.clinic-setup')
                ->withErrors(['error' => 'Please complete clinic setup first.']);
        }
        
        return Inertia::render('onboarding/complete', [
            'user' => $user,
            'trial_status' => $this->getTrialStatus($user),
            'clinic' => $clinic ? [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'address' => $clinic->address,
                'formatted_address' => $clinic->formatted_address,
            ] : null,
        ]);
    }

    /**
     * Complete onboarding and redirect to dashboard or specified route
     */
    public function finish(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            $this->logSecurityEvent('Unauthenticated onboarding finish attempt');
            return redirect()->route('login')->withErrors(['error' => 'User not authenticated']);
        }

        try {
            // Ensure user has admin role in their clinic before completing onboarding
            $clinic = $user->clinics->first();
            if ($clinic) {
                $this->ensureUserHasAdminRole($user, $clinic);
            } else {
                // If no clinic exists, this is an error state
                $this->logWebRequest('User completing onboarding without clinic', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                
                return redirect()->route('onboarding.clinic-setup')
                    ->withErrors(['error' => 'Please complete clinic setup before finishing onboarding.']);
            }

            // Mark onboarding as completed
            $user->update(['onboarding_completed_at' => now()]);

            // Log successful onboarding completion
            $this->logWebRequest('User completed onboarding', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'has_license' => $user->has_activated_license
            ]);

            // Check if there's a specific redirect route requested
            $redirectTo = $request->input('redirect_to');

            if ($redirectTo && $redirectTo === '/admin/clinic-management') {
                return redirect()->route('admin.clinic-management')
                    ->with('success', 'Welcome to Medinext! Your account is ready to use.');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Welcome to Medinext! Your account is ready to use.');
        } catch (\Exception $e) {
            $this->handleException($e, 'Onboarding Completion');
            return redirect()->route('onboarding.complete')
                ->withErrors(['error' => 'Failed to complete onboarding. Please try again.']);
        }
    }

    /**
     * Create a default clinic for the user
     */
    private function createDefaultClinic(User $user, Request $request): Clinic
    {
        $clinic = Clinic::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'timezone' => $request->timezone ?? 'Asia/Manila',
            'address' => [
                'street' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
            ],
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'description' => $request->description,
            'settings' => $this->getDefaultClinicSettings(),
        ]);

        // Ensure admin role exists and has all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'description' => 'Full clinic access and management. Can manage clinic operations, staff, and patients.',
            'is_system_role' => false,
        ]);

        // Ensure admin role has all required permissions
        $this->ensureAdminRolePermissions($adminRole);

        // Assign user to clinic with admin role
        UserClinicRole::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'role_id' => $adminRole->id,
            'department' => 'Administration',
            'status' => 'Active',
            'join_date' => now(),
        ]);

        return $clinic;
    }

    /**
     * Get default clinic settings
     */
    private function getDefaultClinicSettings(): array
    {
        return [
            'working_hours' => [
                'monday' => ['08:00', '17:00'],
                'tuesday' => ['08:00', '17:00'],
                'wednesday' => ['08:00', '17:00'],
                'thursday' => ['08:00', '17:00'],
                'friday' => ['08:00', '17:00'],
                'saturday' => ['08:00', '12:00'],
                'sunday' => ['closed']
            ],
            'appointment_duration' => 30,
            'max_appointments_per_day' => 50,
            'allow_online_booking' => true,
            'require_patient_verification' => true
        ];
    }

    /**
     * Get trial status for user
     */
    private function getTrialStatus(User $user): array
    {
        if ($user->has_activated_license) {
            return [
                'type' => 'licensed',
                'status' => 'active',
                'message' => 'License activated',
                'days_remaining' => null,
                'expires_at' => $user->license?->expires_at
            ];
        }

        if ($user->isTrialExpired()) {
            return [
                'type' => 'trial',
                'status' => 'expired',
                'message' => 'Trial expired',
                'days_remaining' => 0,
                'expires_at' => $user->trial_ends_at
            ];
        }

        if ($user->isOnTrial()) {
            $daysRemaining = $user->getTrialDaysRemaining();
            return [
                'type' => 'trial',
                'status' => 'active',
                'message' => 'Free trial active',
                'days_remaining' => $daysRemaining,
                'expires_at' => $user->trial_ends_at,
                'is_low_days' => $daysRemaining <= 3
            ];
        }

        // Default fallback - start trial if not started
        if (!$user->trial_started_at) {
            $user->startTrial();
        }

        return [
            'type' => 'trial',
            'status' => 'active',
            'message' => 'Free trial active',
            'days_remaining' => 14,
            'expires_at' => $user->trial_ends_at
        ];
    }

    /**
     * Ensure admin role has all required permissions
     */
    private function ensureAdminRolePermissions(Role $adminRole): void
    {
        // Define all admin permissions as per the DemoAccountSeeder (80+ permissions)
        $adminPermissions = [
            // Clinic Management (full control within clinic)
            'clinics.manage', 'clinics.view', 'clinics.edit',

            // User Management (clinic staff management)
            'users.manage', 'users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate', 'users.deactivate',

            // Role Management (view and basic management)
            'roles.view', 'roles.create', 'roles.edit',

            // Doctor Management (full control)
            'doctors.manage', 'doctors.view', 'doctors.create', 'doctors.edit', 'doctors.delete',

            // Patient Management (full control)
            'patients.manage', 'patients.view', 'patients.create', 'patients.edit', 'patients.delete',

            // Clinical Operations (full control)
            'appointments.manage', 'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.delete', 'appointments.checkin',
            'encounters.manage', 'encounters.view', 'encounters.create', 'encounters.edit', 'encounters.delete', 'encounters.complete',
            'prescriptions.manage', 'prescriptions.view', 'prescriptions.create', 'prescriptions.edit', 'prescriptions.delete', 'prescriptions.download',
            'medical_records.manage', 'medical_records.view', 'medical_records.create', 'medical_records.edit', 'medical_records.delete',
            'lab_results.manage', 'lab_results.view', 'lab_results.create', 'lab_results.edit', 'lab_results.delete',
            'file_assets.manage', 'file_assets.view', 'file_assets.upload', 'file_assets.download', 'file_assets.delete',

            // Queue Management (full control)
            'queue.manage', 'queue.view', 'queue.add', 'queue.remove', 'queue.process',

            // Infrastructure Management (full control)
            'rooms.manage', 'rooms.view', 'rooms.create', 'rooms.edit', 'rooms.delete',
            'insurance.manage', 'insurance.view', 'insurance.create', 'insurance.edit', 'insurance.delete',

            // Billing & Financial (full control)
            'billing.manage', 'billing.view', 'billing.create', 'billing.edit', 'billing.delete',

            // Reporting & Analytics (full access)
            'reports.view', 'reports.export', 'reports.generate',
            'activity_logs.view', 'activity_logs.export',

            // Scheduling (full control)
            'schedule.view', 'schedule.manage',

            // Notifications (full control)
            'notifications.manage', 'notifications.view', 'notifications.create', 'notifications.edit', 'notifications.delete',

            // Settings (clinic level management)
            'settings.manage', 'settings.view',

            // Dashboard & Search (full access)
            'dashboard.view', 'dashboard.stats',
            'search.global', 'search.patients', 'search.doctors',

            // Profile Management
            'profile.view', 'profile.edit',

            // Additional Admin-specific permissions
            'permissions.view', // View permissions for role management
            'system.info', // View system information
        ];

        // Get permission IDs
        $permissionIds = \App\Models\Permission::whereIn('slug', $adminPermissions)->pluck('id');
        
        // Sync permissions to admin role
        $adminRole->permissions()->sync($permissionIds);
        
        // Update role configuration
        $adminRole->update([
            'permissions_config' => $adminPermissions
        ]);
    }

    /**
     * Ensure user has admin role with proper permissions in the clinic
     */
    private function ensureUserHasAdminRole(User $user, Clinic $clinic): void
    {
        // Get or create admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'description' => 'Full clinic access and management. Can manage clinic operations, staff, and patients.',
            'is_system_role' => false,
        ]);

        // Ensure admin role has all required permissions
        $this->ensureAdminRolePermissions($adminRole);

        // Check if user already has admin role in this clinic
        $existingRole = UserClinicRole::where('user_id', $user->id)
            ->where('clinic_id', $clinic->id)
            ->where('role_id', $adminRole->id)
            ->first();

        if (!$existingRole) {
            // Assign admin role to user in this clinic
            UserClinicRole::create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'role_id' => $adminRole->id,
                'department' => 'Administration',
                'status' => 'Active',
                'join_date' => now(),
            ]);
        }
    }

    /**
     * Validate onboarding progress and redirect to appropriate step
     */
    private function validateOnboardingProgress(User $user): ?string
    {
        $clinic = $user->clinics->first();
        
        // If no clinic exists, redirect to clinic setup
        if (!$clinic) {
            return 'onboarding.clinic-setup';
        }

        // If clinic exists but user doesn't have admin role, ensure they do
        if ($clinic && !$user->isAdminInClinic($clinic->id)) {
            $this->ensureUserHasAdminRole($user, $clinic);
        }

        return null; // No redirect needed
    }
}
