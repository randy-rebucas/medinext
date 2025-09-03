<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
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

class Doctor extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Doctor>
     */
    public static $model = \App\Models\Doctor::class;

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
        'id', 'specialty', 'license_no'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Doctors';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Doctor';
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

            BelongsTo::make('User')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:users,id'),

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:clinics,id'),

            Select::make('Specialty')
                ->options([
                    'Cardiology' => 'Cardiology',
                    'Dermatology' => 'Dermatology',
                    'Endocrinology' => 'Endocrinology',
                    'Gastroenterology' => 'Gastroenterology',
                    'General Practice' => 'General Practice',
                    'Internal Medicine' => 'Internal Medicine',
                    'Neurology' => 'Neurology',
                    'Obstetrics & Gynecology' => 'Obstetrics & Gynecology',
                    'Oncology' => 'Oncology',
                    'Ophthalmology' => 'Ophthalmology',
                    'Orthopedics' => 'Orthopedics',
                    'Pediatrics' => 'Pediatrics',
                    'Psychiatry' => 'Psychiatry',
                    'Pulmonology' => 'Pulmonology',
                    'Radiology' => 'Radiology',
                    'Surgery' => 'Surgery',
                    'Urology' => 'Urology'
                ])
                ->sortable()
                ->rules('required'),

            Text::make('License Number', 'license_no')
                ->sortable()
                ->rules('required', 'max:100', 'unique:doctors,license_no,{{resourceId}}')
                ->help('Medical license number'),

            Text::make('Signature URL', 'signature_url')
                ->nullable()
                ->hideFromIndex()
                ->help('URL to doctor\'s digital signature'),

            DateTime::make('Created At')
                ->sortable()
                ->hideFromForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->hideFromForms(),

            HasMany::make('Appointments'),
            HasMany::make('Encounters'),
            HasMany::make('Prescriptions'),
            HasMany::make('Lab Results'),
            HasMany::make('Medrep Visits', 'medrepVisits'),
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
