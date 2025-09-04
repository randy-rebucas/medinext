<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Date;
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

class Patient extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Patient>
     */
    public static $model = \App\Models\Patient::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'first_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'first_name', 'last_name', 'code'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Patients';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Patient';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'patients';
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
        return $query->with(['clinic', 'appointments', 'encounters']);
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
        return $query->with(['clinic', 'appointments.doctor', 'encounters.doctor', 'prescriptions', 'labResults']);
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

            Text::make('Code')
                ->sortable()
                ->rules('required', 'max:50'),

            Text::make('First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Last Name', 'last_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Date::make('Date of Birth', 'dob')
                ->sortable()
                ->rules('required', 'date', 'before:today'),

            Select::make('Sex')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                    'prefer_not_to_say' => 'Prefer not to say'
                ])
                ->sortable()
                ->rules('required'),

            BelongsTo::make('Clinic')
                ->searchable()
                ->sortable(),

            \Laravel\Nova\Fields\Text::make('Full Name', function () {
                return $this->first_name . ' ' . $this->last_name;
            })->sortable()
                ->exceptOnForms(),

            \Laravel\Nova\Fields\Number::make('Age', function () {
                return $this->dob ? $this->dob->age : null;
            })->sortable()
                ->exceptOnForms(),

            \Laravel\Nova\Fields\Number::make('Total Appointments', function () {
                return $this->appointments()->count();
            })->sortable()
                ->exceptOnForms(),

            \Laravel\Nova\Fields\Number::make('Total Encounters', function () {
                return $this->encounters()->count();
            })->sortable()
                ->exceptOnForms(),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            HasMany::make('Appointments')
                ->hideFromIndex(),
            HasMany::make('Encounters')
                ->hideFromIndex(),
            HasMany::make('Prescriptions')
                ->hideFromIndex(),
            HasMany::make('Lab Results')
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
