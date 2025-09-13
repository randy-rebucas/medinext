<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
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
        $user = Auth::user();

        return Inertia::render('onboarding/welcome', [
            'user' => $user,
            'trial_status' => [
                'type' => 'trial',
                'status' => 'active',
                'message' => 'Free trial active',
                'days_remaining' => 30
            ],
            'clinic' => $user ? $user->clinics->first() : null,
        ]);
    }

    /**
     * Show the license activation step
     */
    public function license(): Response
    {
        $user = Auth::user();
        $licenseInfo = $this->licenseService->getLicenseInfo();

        return Inertia::render('onboarding/license', [
            'user' => $user,
            'trial_status' => [
                'type' => 'trial',
                'status' => 'active',
                'message' => 'Free trial active',
                'days_remaining' => 30
            ],
            'license_info' => $licenseInfo,
        ]);
    }

    /**
     * Handle license activation
     */
    public function activateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $licenseKey = $request->license_key;

        // Validate and activate the license
        $result = $this->licenseService->activateLicenseForUser($user, $licenseKey);

        if (!$result['success']) {
            return back()->withErrors([
                'license_key' => $result['message']
            ])->withInput();
        }

        return redirect()->route('onboarding.complete')
            ->with('success', 'License activated successfully!');
    }

    /**
     * Show the clinic setup step
     */
    public function clinicSetup(): Response
    {
        $user = Auth::user();
        $clinic = $user ? $user->clinics->first() : null;

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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $clinic = $user ? $user->clinics->first() : null;

        if ($clinic) {
            $clinic->update([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'description' => $request->description,
            ]);
        }

        return redirect()->route('onboarding.complete')
            ->with('success', 'Clinic information updated successfully!');
    }

    /**
     * Show the team setup step
     */
    public function teamSetup(): Response
    {
        $user = Auth::user();
        $clinic = $user ? $user->clinics->first() : null;
        $roles = Role::where('is_system_role', false)->get();

        return Inertia::render('onboarding/team-setup', [
            'user' => $user,
            'clinic' => $clinic,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the completion step
     */
    public function complete(): Response
    {
        $user = Auth::user();

        return Inertia::render('onboarding/complete', [
            'user' => $user,
            'trial_status' => [
                'type' => 'trial',
                'status' => 'active',
                'message' => 'Free trial active',
                'days_remaining' => 30
            ],
            'clinic' => $user ? $user->clinics->first() : null,
        ]);
    }

    /**
     * Complete onboarding and redirect to dashboard or specified route
     */
    public function finish(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Mark onboarding as completed
        if ($user) {
            $user->update(['onboarding_completed_at' => now()]);
        }

        // Check if there's a specific redirect route requested
        $redirectTo = $request->input('redirect_to');

        if ($redirectTo && $redirectTo === '/admin/clinic-settings') {
            return redirect()->route('admin.clinic-settings')
                ->with('success', 'Welcome to Medinext! Your account is ready to use.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to Medinext! Your account is ready to use.');
    }
}
