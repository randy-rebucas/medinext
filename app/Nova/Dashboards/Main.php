<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\Partition;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Role;
use App\Nova\Metrics\PatientsPerClinic;
use App\Nova\Metrics\AppointmentTrends;
use App\Nova\Metrics\TotalPatients;
use App\Nova\Metrics\TotalDoctors;
use App\Nova\Metrics\TotalClinics;
use App\Nova\Metrics\TotalUsers;
use App\Nova\Metrics\TodaysAppointments;
use App\Nova\Metrics\ActivePrescriptions;
use App\Nova\Metrics\TotalEncounters;
use App\Nova\Metrics\PatientGrowthTrend;
use App\Nova\Metrics\AppointmentStatusDistribution;

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
            // Core metrics - First row
            new TotalPatients,

            new TotalDoctors,

            new TotalClinics,

            new TotalUsers,

            // Activity metrics - Second row
            new TodaysAppointments,

            new ActivePrescriptions,

            new TotalEncounters,

            // Trend metrics - Third row
            new AppointmentTrends,

            new PatientGrowthTrend,

            // Distribution metrics - Fourth row
            new PatientsPerClinic,

            new AppointmentStatusDistribution,
        ];
    }
}
