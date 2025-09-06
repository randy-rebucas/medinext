<?php

namespace App\Nova\Metrics;

use App\Models\License;
use App\Services\LicenseService;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Laravel\Nova\Nova;

class LicenseStatus extends Value
{
    protected $licenseService;

    public function __construct()
    {
        $this->licenseService = app(LicenseService::class);
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        $license = $this->licenseService->getCurrentLicense();
        
        if (!$license) {
            return $this->result('No License')
                ->format('0,0');
        }

        $status = $license->status;

        $label = match ($status) {
            'active' => 'Active',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            'revoked' => 'Revoked',
            default => ucfirst($status),
        };

        return $this->result($label)
            ->format('0,0');
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return 'License Status';
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int|string, string>
     */
    public function ranges(): array
    {
        return [];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     */
    public function cacheFor(): DateTimeInterface|null
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'license-status';
    }
}
