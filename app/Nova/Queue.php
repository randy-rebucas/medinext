<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;

class Queue extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Queue>
     */
    public static $model = \App\Models\Queue::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'description', 'queue_type'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Queues';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Queue';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'queues';
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
        return $query->with(['clinic', 'patients']);
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
        return $query->with(['clinic', 'patients.patient', 'createdBy', 'updatedBy']);
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

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex(),

            BelongsTo::make('Clinic')
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:clinics,id'),

            Select::make('Queue Type', 'queue_type')
                ->options([
                    'general' => 'General',
                    'urgent' => 'Urgent',
                    'follow_up' => 'Follow Up',
                    'consultation' => 'Consultation',
                    'procedure' => 'Procedure',
                    'emergency' => 'Emergency'
                ])
                ->sortable()
                ->rules('required'),

            Select::make('Status')
                ->options([
                    'active' => 'Active',
                    'paused' => 'Paused',
                    'closed' => 'Closed',
                    'maintenance' => 'Maintenance'
                ])
                ->sortable()
                ->rules('required'),

            Number::make('Max Capacity')
                ->sortable()
                ->rules('required', 'integer', 'min:1')
                ->help('Maximum number of patients allowed in queue'),

            Number::make('Current Count')
                ->sortable()
                ->exceptOnForms()
                ->help('Current number of patients in queue'),

            Number::make('Average Wait Time', 'average_wait_time')
                ->sortable()
                ->rules('integer', 'min:0')
                ->help('Average wait time in minutes'),

            Number::make('Estimated Wait Time', 'estimated_wait_time')
                ->sortable()
                ->exceptOnForms()
                ->help('Current estimated wait time in minutes'),

            Number::make('Priority Level', 'priority_level')
                ->sortable()
                ->rules('integer', 'min:1', 'max:5')
                ->help('Priority level (1-5, 5 being highest)'),

            Boolean::make('Is Active', 'is_active')
                ->sortable(),

            Boolean::make('Auto Assign', 'auto_assign')
                ->help('Automatically assign patients to available doctors'),

            KeyValue::make('Settings')
                ->nullable()
                ->hideFromIndex()
                ->help('Queue configuration settings'),

            BelongsTo::make('Created By', 'createdBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            BelongsTo::make('Updated By', 'updatedBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Text::make('Available Slots', function () {
                return $this->available_slots;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Text::make('Wait Time Formatted', function () {
                return $this->wait_time_formatted;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Badge::make('Status')
                ->map([
                    'active' => 'success',
                    'paused' => 'warning',
                    'closed' => 'danger',
                    'maintenance' => 'info',
                ]),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            HasMany::make('Patients', 'patients', QueuePatient::class)
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
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
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
