<?php

namespace App\Nova\Metrics;

use App\Models\Encounter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class TotalEncounters extends Value
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Total Encounters');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = Encounter::query();

        if ($request->range === 'TODAY') {
            $query->whereDate('date', today());
        } elseif ($request->range === 'MTD') {
            $query->whereYear('date', now()->year)
                  ->whereMonth('date', now()->month);
        } elseif ($request->range === 'QTD') {
            $query->whereYear('date', now()->year)
                  ->whereQuarter('date', now()->quarter);
        } elseif ($request->range === 'YTD') {
            $query->whereYear('date', now()->year);
        }

        return $this->result($query->count());
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            'TODAY' => __('Today'),
            'MTD' => __('Month To Date'),
            'QTD' => __('Quarter To Date'),
            'YTD' => __('Year To Date'),
        ];
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
        return 'total-encounters';
    }
}
