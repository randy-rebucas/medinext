<?php

namespace App\Nova\Filters;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class DoctorFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

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
        return $query->where('doctor_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return Doctor::with('user')
            ->get()
            ->mapWithKeys(function ($doctor) {
                return [$doctor->user->name ?? "Doctor #{$doctor->id}" => $doctor->id];
            })
            ->toArray();
    }

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __('Doctor');
    }
}
