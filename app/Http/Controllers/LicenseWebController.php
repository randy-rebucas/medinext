<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Validator;

class LicenseWebController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Show license activation form
     */
    public function showActivationForm()
    {
        return view('license.activate');
    }

    /**
     * Handle license activation
     */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|max:255',
            'activation_code' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $this->licenseService->activateLicense(
            $request->license_key,
            $request->activation_code
        );

        if ($result['success']) {
            return redirect()->route('dashboard')
                ->with('success', 'License activated successfully!');
        }

        return redirect()->back()
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Show license error page
     */
    public function error(Request $request)
    {
        $error = $request->session()->get('error', 'License validation failed');
        
        return view('license.error', [
            'error' => $error,
            'licenseStatus' => $this->licenseService->getLicenseStatus()
        ]);
    }

    /**
     * Show license status page
     */
    public function status()
    {
        $licenseInfo = $this->licenseService->getLicenseInfo();
        $licenseStatus = $this->licenseService->getLicenseStatus();

        return view('license.status', [
            'licenseInfo' => $licenseInfo,
            'licenseStatus' => $licenseStatus
        ]);
    }

    /**
     * Show license management page (admin only)
     */
    public function manage()
    {
        $licenseInfo = $this->licenseService->getLicenseInfo();
        $licenseStatus = $this->licenseService->getLicenseStatus();
        $statistics = $this->licenseService->getLicenseStatistics();

        return view('license.manage', [
            'licenseInfo' => $licenseInfo,
            'licenseStatus' => $licenseStatus,
            'statistics' => $statistics
        ]);
    }

    /**
     * Show license usage page
     */
    public function usage()
    {
        $licenseInfo = $this->licenseService->getLicenseInfo();
        $licenseStatus = $this->licenseService->getLicenseStatus();

        return view('license.usage', [
            'licenseInfo' => $licenseInfo,
            'licenseStatus' => $licenseStatus
        ]);
    }
}