<?php

namespace App\Nova\Metrics;

use App\Models\Prescription;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class PrescriptionStatusDistribution extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Prescription Status Distribution');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = Prescription::query();

        if ($request->range === 'TODAY') {
            $query->whereDate('issued_at', today());
        } elseif ($request->range === 'MTD') {
            $query->whereYear('issued_at', now()->year)
                  ->whereMonth('issued_at', now()->month);
        } elseif ($request->range === 'QTD') {
            $query->whereYear('issued_at', now()->year)
                  ->whereQuarter('issued_at', now()->quarter);
        } elseif ($request->range === 'YTD') {
            $query->whereYear('issued_at', now()->year);
        }

        return $this->result($query->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    ucfirst(str_replace('_', ' ', $item->status)) => $item->count
                ];
            })
            ->toArray());
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
        return 'prescription-status-distribution';
    }
}
