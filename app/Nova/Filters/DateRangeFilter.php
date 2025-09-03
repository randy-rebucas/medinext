<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class DateRangeFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'date-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if (isset($value['from']) && isset($value['to'])) {
            return $query->whereBetween('created_at', [$value['from'], $value['to']]);
        }
        
        if (isset($value['from'])) {
            return $query->where('created_at', '>=', $value['from']);
        }
        
        if (isset($value['to'])) {
            return $query->where('created_at', '<=', $value['to']);
        }
        
        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Today' => 'today',
            'This Week' => 'this_week',
            'This Month' => 'this_month',
            'Last Month' => 'last_month',
            'This Year' => 'this_year',
        ];
    }
}
