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
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Image;
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
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'doctors';
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
                ->rules('required', 'exists:users,id')
                ->help('Select the user account for this doctor'),

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:clinics,id')
                ->help('Select the clinic where this doctor works'),

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
                ->rules('required')
                ->help('Doctor\'s medical specialty'),

            Text::make('License Number', 'license_no')
                ->sortable()
                ->rules('required', 'max:100', 'unique:doctors,license_no,{{resourceId}}')
                ->help('Medical license number'),

            Boolean::make('Active', 'is_active')
                ->default(true)
                ->sortable()
                ->help('Whether this doctor is currently active'),

            Number::make('Consultation Fee', 'consultation_fee')
                ->step(0.01)
                ->nullable()
                ->hideFromIndex()
                ->help('Standard consultation fee for this doctor'),

            Code::make('Availability Schedule', 'availability_schedule')
                ->language('json')
                ->nullable()
                ->hideFromIndex()
                ->help('Weekly availability schedule in JSON format'),

            Image::make('Signature', 'signature_url')
                ->disk('public')
                ->path('doctors/signatures')
                ->nullable()
                ->hideFromIndex()
                ->help('Digital signature image'),

            DateTime::make('Created At')
                ->sortable()
                ->hideFromForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->hideFromForms(),

            // Relationships
            HasMany::make('Appointments', 'appointments', Appointment::class),
            HasMany::make('Encounters', 'encounters', Encounter::class),
            HasMany::make('Prescriptions', 'prescriptions', Prescription::class),
            HasMany::make('Lab Results', 'labResults', LabResult::class),
            HasMany::make('Medrep Visits', 'medrepVisits', MedrepVisit::class),
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
     * @return array<int, \Laravel\Nova\Filter>
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
     * @return array<int, \Laravel\Nova\Lens>
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
     * @return array<int, \Laravel\Nova\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new ExportData,
            new BulkUpdate,
        ];
    }
}
