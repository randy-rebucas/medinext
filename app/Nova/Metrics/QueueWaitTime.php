<?php

namespace App\Nova\Metrics;

use App\Models\QueuePatient;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class QueueWaitTime extends Value
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Average Queue Wait Time');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = QueuePatient::where('status', 'served')
            ->whereNotNull('actual_wait_time');

        if ($request->range === 'TODAY') {
            $query->whereDate('served_at', today());
        } elseif ($request->range === 'MTD') {
            $query->whereYear('served_at', now()->year)
                  ->whereMonth('served_at', now()->month);
        } elseif ($request->range === 'QTD') {
            $query->whereYear('served_at', now()->year)
                  ->whereQuarter('served_at', now()->quarter);
        } elseif ($request->range === 'YTD') {
            $query->whereYear('served_at', now()->year);
        }

        $averageWaitTime = $query->avg('actual_wait_time');

        return $this->result(round($averageWaitTime ?? 0, 1))
            ->format('0.0')
            ->suffix(' minutes');
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
        return 'queue-wait-time';
    }
}
