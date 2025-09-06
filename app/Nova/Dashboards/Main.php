<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\LicenseStatus;
use App\Nova\Metrics\LicenseUsage;
use App\Nova\Metrics\LicenseExpiration;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(): array
    {
        return [
            new LicenseStatus,
            new LicenseUsage,
            new LicenseExpiration,
            new Help,
        ];
    }
}
