<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class Appointment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Appointment>
     */
    public static $model = \App\Models\Appointment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'reason', 'status'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Appointments';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Appointment';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'appointments';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Patient')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:patients,id'),

            BelongsTo::make('Doctor')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:doctors,id'),

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:clinics,id'),

            BelongsTo::make('Room')
                ->sortable()
                ->searchable()
                ->nullable()
                ->rules('nullable', 'exists:rooms,id'),

            DateTime::make('Start Time', 'start_at')
                ->sortable()
                ->rules('required', 'date')
                ->help('Appointment start time'),

            DateTime::make('End Time', 'end_at')
                ->sortable()
                ->rules('required', 'date', 'after:start_at')
                ->help('Appointment end time'),

            Select::make('Status')
                ->options([
                    'scheduled' => 'Scheduled',
                    'confirmed' => 'Confirmed',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'no_show' => 'No Show'
                ])
                ->sortable()
                ->rules('required')
                ->help('Current appointment status'),

            Select::make('Source')
                ->options([
                    'walk_in' => 'Walk In',
                    'phone' => 'Phone',
                    'online' => 'Online',
                    'referral' => 'Referral',
                    'follow_up' => 'Follow Up'
                ])
                ->sortable()
                ->rules('required')
                ->help('How the appointment was booked'),

            Textarea::make('Reason')
                ->nullable()
                ->hideFromIndex()
                ->help('Reason for the appointment'),

            Textarea::make('Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional notes about the appointment'),

            Number::make('Duration (minutes)')
                ->computed(function () {
                    if ($this->start_at && $this->end_at) {
                        return $this->start_at->diffInMinutes($this->end_at);
                    }
                    return null;
                })
                ->sortable()
                ->hideFromForms()
                ->help('Calculated duration in minutes'),

            DateTime::make('Created At')
                ->sortable()
                ->hideFromForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->hideFromForms(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new StatusFilter,
            new DateRangeFilter,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [
            new ActiveRecords,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new ExportData,
            new BulkUpdate,
        ];
    }
}
