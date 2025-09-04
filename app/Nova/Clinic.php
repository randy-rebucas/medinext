<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class Clinic extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Clinic>
     */
    public static $model = \App\Models\Clinic::class;

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
        'id', 'name', 'address', 'phone', 'email'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Clinics';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Clinic';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'clinics';
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
                ->rules('required', 'max:255')
                ->help('Enter the clinic name'),

            Text::make('Slug')
                ->nullable()
                ->hideFromIndex()
                ->help('URL-friendly identifier for the clinic'),

            Select::make('Timezone')
                ->options([
                    'Asia/Manila' => 'Asia/Manila (Philippines)',
                    'Asia/Tokyo' => 'Asia/Tokyo (Japan)',
                    'Asia/Seoul' => 'Asia/Seoul (South Korea)',
                    'Asia/Singapore' => 'Asia/Singapore',
                    'Asia/Bangkok' => 'Asia/Bangkok (Thailand)',
                    'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh (Vietnam)',
                    'Asia/Jakarta' => 'Asia/Jakarta (Indonesia)',
                    'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur (Malaysia)',
                    'UTC' => 'UTC (Coordinated Universal Time)',
                ])
                ->default('Asia/Manila')
                ->sortable()
                ->rules('required'),

            Image::make('Logo', 'logo_url')
                ->disk('public')
                ->path('clinics/logos')
                ->nullable()
                ->hideFromIndex()
                ->help('Upload clinic logo (recommended: 200x200px)'),

            Text::make('Phone')
                ->sortable()
                ->rules('required', 'max:20')
                ->help('Primary contact phone number'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->help('Primary contact email address'),

            URL::make('Website')
                ->nullable()
                ->hideFromIndex()
                ->help('Clinic website URL'),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex()
                ->help('Brief description of the clinic'),

            Code::make('Address')
                ->language('json')
                ->nullable()
                ->hideFromIndex()
                ->help('Clinic address in JSON format (street, city, state, country)'),

            Code::make('Settings')
                ->language('json')
                ->nullable()
                ->hideFromIndex()
                ->help('Clinic-specific settings and configuration'),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),

            // Relationships
            HasMany::make('Doctors', 'doctors', Doctor::class),
            HasMany::make('Patients', 'patients', Patient::class),
            HasMany::make('Rooms', 'rooms', Room::class),
            HasMany::make('Appointments', 'appointments', Appointment::class),
            HasMany::make('User Clinic Roles', 'userClinicRoles', UserClinicRole::class),
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
