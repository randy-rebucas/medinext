<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class Encounter extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Encounter>
     */
    public static $model = \App\Models\Encounter::class;

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
        'id', 'type', 'status', 'notes_soap'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Encounters';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Encounter';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'encounters';
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

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:clinics,id'),

            BelongsTo::make('Patient')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:patients,id'),

            BelongsTo::make('Doctor')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:doctors,id'),

            Date::make('Date')
                ->sortable()
                ->rules('required', 'date')
                ->help('Date of the encounter'),

            Select::make('Type')
                ->options([
                    'consultation' => 'Consultation',
                    'examination' => 'Examination',
                    'procedure' => 'Procedure',
                    'emergency' => 'Emergency',
                    'follow_up' => 'Follow Up',
                    'routine' => 'Routine Check',
                    'urgent' => 'Urgent Care'
                ])
                ->sortable()
                ->rules('required')
                ->help('Type of medical encounter'),

            Select::make('Status')
                ->options([
                    'scheduled' => 'Scheduled',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'no_show' => 'No Show'
                ])
                ->sortable()
                ->rules('required')
                ->help('Current encounter status'),

            Textarea::make('SOAP Notes', 'notes_soap')
                ->nullable()
                ->hideFromIndex()
                ->help('Subjective, Objective, Assessment, Plan notes'),

            Code::make('Vitals')
                ->json()
                ->nullable()
                ->hideFromIndex()
                ->help('Patient vital signs (JSON format)'),

            Code::make('Diagnosis Codes', 'diagnosis_codes')
                ->json()
                ->nullable()
                ->hideFromIndex()
                ->help('ICD diagnosis codes (JSON format)'),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),

            HasMany::make('Prescriptions'),
            HasMany::make('Lab Results'),
            HasMany::make('File Assets'),
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
