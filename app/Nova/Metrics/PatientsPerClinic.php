<?php

namespace App\Nova\Metrics;

use App\Models\Clinic;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class PatientsPerClinic extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Patients Per Clinic');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        return Clinic::withCount('patients')
            ->get()
            ->mapWithKeys(function ($clinic) {
                return [$clinic->name ?: 'Unnamed Clinic' => $clinic->patients_count];
            })
            ->filter(function ($count) {
                return $count > 0;
            });
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'patients-per-clinic';
    }
}
