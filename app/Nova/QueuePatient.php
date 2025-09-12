<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;

class QueuePatient extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\QueuePatient>
     */
    public static $model = \App\Models\QueuePatient::class;

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
        'id', 'status'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Queue Patients';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Queue Patient';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'queue-patients';
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->with(['queue', 'patient']);
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->with(['queue', 'patient']);
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

            BelongsTo::make('Queue')
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:queues,id'),

            BelongsTo::make('Patient')
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:patients,id'),

            Select::make('Status')
                ->options([
                    'waiting' => 'Waiting',
                    'called' => 'Called',
                    'served' => 'Served',
                    'removed' => 'Removed',
                    'cancelled' => 'Cancelled'
                ])
                ->sortable()
                ->rules('required'),

            Number::make('Priority')
                ->sortable()
                ->rules('required', 'integer', 'min:1', 'max:5')
                ->help('Priority level (1-5, 5 being highest)'),

            DateTime::make('Joined At', 'joined_at')
                ->sortable()
                ->rules('required', 'date'),

            DateTime::make('Called At', 'called_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms(),

            DateTime::make('Served At', 'served_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms(),

            DateTime::make('Removed At', 'removed_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms(),

            KeyValue::make('Metadata')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional metadata for this queue entry'),

            \Laravel\Nova\Fields\Text::make('Patient Name', function () {
                return $this->patient ? $this->patient->full_name : 'N/A';
            })->onlyOnIndex(),

            \Laravel\Nova\Fields\Text::make('Queue Name', function () {
                return $this->queue ? $this->queue->name : 'N/A';
            })->onlyOnIndex(),

            \Laravel\Nova\Fields\Number::make('Wait Time (minutes)', function () {
                if ($this->served_at && $this->joined_at) {
                    return $this->joined_at->diffInMinutes($this->served_at);
                } elseif ($this->called_at && $this->joined_at) {
                    return $this->joined_at->diffInMinutes($this->called_at);
                }
                return null;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Badge::make('Status')
                ->map([
                    'waiting' => 'warning',
                    'called' => 'info',
                    'served' => 'success',
                    'removed' => 'danger',
                    'cancelled' => 'danger',
                ]),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),
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
            new \App\Nova\Filters\QueueStatusFilter,
            new \App\Nova\Filters\PatientFilter,
            new \App\Nova\Filters\ClinicFilter,
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
            new \App\Nova\Lenses\QueueStatus,
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
            new \App\Nova\Actions\CallNextPatient,
            new \App\Nova\Actions\ServePatient,
        ];
    }
}
