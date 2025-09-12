<?php

namespace App\Nova\Lenses;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class TodaysAppointments extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query->whereDate('start_at', today())
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            BelongsTo::make('Patient')
                ->sortable()
                ->searchable(),

            BelongsTo::make('Doctor')
                ->sortable()
                ->searchable(),

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable(),

            DateTime::make('Start Time', 'start_at')
                ->sortable()
                ->format('HH:mm'),

            DateTime::make('End Time', 'end_at')
                ->sortable()
                ->format('HH:mm'),

            Badge::make('Status')
                ->map([
                    'scheduled' => 'info',
                    'confirmed' => 'success',
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    'no_show' => 'danger',
                ]),

            Text::make('Reason')
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new \App\Nova\Filters\AppointmentStatusFilter,
            new \App\Nova\Filters\DoctorFilter,
            new \App\Nova\Filters\ClinicFilter,
        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the displayable name of the lens.
     *
     * @return string
     */
    public function name()
    {
        return __('Today\'s Appointments');
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'todays-appointments';
    }
}
