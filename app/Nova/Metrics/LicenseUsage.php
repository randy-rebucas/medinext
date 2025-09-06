<?php

namespace App\Nova\Metrics;

use App\Services\LicenseService;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class LicenseUsage extends Value
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
            return $this->result(0)
                ->format('0,0');
        }

        $usagePercentage = $license->getUsagePercentage('users');

        // Ensure we have a valid number
        if (!is_numeric($usagePercentage) || is_nan($usagePercentage)) {
            $usagePercentage = 0;
        }

        return $this->result($usagePercentage)
            ->format('0,0')
            ->suffix('%');
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return 'User Usage';
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
        return 'license-usage';
    }
}
