<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\LicenseStatus;
use App\Nova\Metrics\LicenseUsage;
use App\Nova\Metrics\LicenseExpiration;
use App\Nova\Metrics\TotalPatients;
use App\Nova\Metrics\TotalDoctors;
use App\Nova\Metrics\TotalClinics;
use App\Nova\Metrics\TodaysAppointments;
use App\Nova\Metrics\AppointmentCompletionRate;
use App\Nova\Metrics\PatientGrowthTrend;
use App\Nova\Metrics\RevenueTrend;
use App\Nova\Metrics\QueueWaitTime;
use App\Nova\Metrics\PrescriptionStatusDistribution;
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
            // License Management
            new LicenseStatus,
            new LicenseUsage,
            new LicenseExpiration,

            // Core Metrics
            new TotalPatients,
            new TotalDoctors,
            new TotalClinics,
            new TodaysAppointments,

            // Performance Metrics
            new AppointmentCompletionRate,
            new PatientGrowthTrend,
            new RevenueTrend,
            new QueueWaitTime,
            new PrescriptionStatusDistribution,

            new Help,
        ];
    }
}
