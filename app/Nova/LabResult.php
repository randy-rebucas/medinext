<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
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

class LabResult extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\LabResult>
     */
    public static $model = \App\Models\LabResult::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'test_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'test_type', 'test_name', 'status', 'patient_id'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Lab Results';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Lab Result';
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

            BelongsTo::make('Encounter')
                ->nullable()
                ->searchable()
                ->rules('nullable', 'exists:encounters,id'),

            Select::make('Test Type')
                ->options([
                    'blood' => 'Blood Tests',
                    'urine' => 'Urine Tests',
                    'stool' => 'Stool Tests',
                    'xray' => 'Imaging - X-Ray',
                    'mri' => 'Imaging - MRI',
                    'ct' => 'Imaging - CT',
                    'ultrasound' => 'Imaging - Ultrasound',
                    'ecg' => 'Cardiac - ECG',
                    'echo' => 'Cardiac - Echocardiogram',
                    'biopsy' => 'Pathology - Biopsy',
                    'culture' => 'Microbiology - Culture',
                    'other' => 'Other Tests',
                ])
                ->sortable()
                ->rules('required')
                ->help('Category of laboratory test'),

            Text::make('Test Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Name of the specific test performed'),

            Text::make('Result Value')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Actual test result value'),

            Text::make('Unit')
                ->sortable()
                ->rules('nullable', 'max:50')
                ->help('Unit of measurement for the result'),

            Text::make('Reference Range')
                ->sortable()
                ->rules('nullable', 'max:255')
                ->help('Normal reference range for the test'),

            Select::make('Status')
                ->options([
                    'pending' => 'Pending',
                    'normal' => 'Normal',
                    'abnormal' => 'Abnormal',
                    'critical' => 'Critical',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled'
                ])
                ->sortable()
                ->rules('required')
                ->help('Current status of the lab result'),

            DateTime::make('Ordered At')
                ->sortable()
                ->rules('required', 'date')
                ->help('When the test was ordered'),

            DateTime::make('Completed At')
                ->nullable()
                ->sortable()
                ->rules('nullable', 'date')
                ->help('When the test was completed'),

            Textarea::make('Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional notes about the lab result'),

            BelongsTo::make('Ordered By Doctor', 'orderedByDoctor', Doctor::class)
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:doctors,id')
                ->help('Doctor who ordered the test'),

            BelongsTo::make('Reviewed By Doctor', 'reviewedByDoctor', Doctor::class)
                ->nullable()
                ->sortable()
                ->searchable()
                ->rules('nullable', 'exists:doctors,id')
                ->help('Doctor who reviewed the result'),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),

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
            new \App\Nova\Filters\DoctorFilter,
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
            new ActiveRecords,
            new \App\Nova\Lenses\RecentLabResults,
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
            new \App\Nova\Actions\ApproveLabResults,
        ];
    }
}
