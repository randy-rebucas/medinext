<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class TotalUsers extends Value
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Total Users');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = User::query();

        if ($request->range === 'TODAY') {
            $query->whereDate('created_at', today());
        } elseif ($request->range === 'MTD') {
            $query->whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month);
        } elseif ($request->range === 'QTD') {
            $query->whereYear('created_at', now()->year)
                  ->whereQuarter('created_at', now()->quarter);
        } elseif ($request->range === 'YTD') {
            $query->whereYear('created_at', now()->year);
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
        return 'total-users';
    }
}
