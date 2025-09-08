<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Show the license activation page
     */
    public function showActivation(): Response
    {
        $user = Auth::user();

        return Inertia::render('license/activate', [
            'user' => $user,
            'trial_status' => $user->getAccessStatus(), // @phpstan-ignore-line
        ]);
    }

    /**
     * Handle license activation
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $licenseKey = $request->license_key;

        // Validate the license key
        $validation = $this->licenseService->validateLicense($licenseKey, $user);

        if (!$validation['valid']) {
            return back()->withErrors([
                'license_key' => $validation['message']
            ])->withInput();
        }

        // Activate the license for the user
        $user->activateLicense($licenseKey); // @phpstan-ignore-line

        return redirect()->route('dashboard')
            ->with('success', 'License activated successfully! You now have full access to the application.');
    }

    /**
     * Show license status page
     */
    public function status(): Response
    {
        $user = Auth::user();
        $licenseInfo = $this->licenseService->getLicenseInfo();

        return Inertia::render('license/status', [
            'user' => $user,
            'license_info' => $licenseInfo,
            'access_status' => $user->getAccessStatus(), // @phpstan-ignore-line
        ]);
    }
}
