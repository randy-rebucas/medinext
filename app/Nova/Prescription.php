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
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class Prescription extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Prescription>
     */
    public static $model = \App\Models\Prescription::class;

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
        'id', 'status', 'qr_hash'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Prescriptions';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Prescription';
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

            BelongsTo::make('Encounter')
                ->nullable()
                ->searchable()
                ->rules('nullable', 'exists:encounters,id'),

            Select::make('Status')
                ->options([
                    'active' => 'Active',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'expired' => 'Expired'
                ])
                ->sortable()
                ->rules('required')
                ->help('Current prescription status'),

            DateTime::make('Issued At', 'issued_at')
                ->sortable()
                ->rules('required', 'date')
                ->help('When the prescription was issued'),

            Text::make('QR Hash', 'qr_hash')
                ->sortable()
                ->rules('required', 'max:255', 'unique:prescriptions,qr_hash,{{resourceId}}')
                ->hideFromIndex()
                ->help('Unique QR code hash for verification'),

            URL::make('PDF URL', 'pdf_url')
                ->nullable()
                ->hideFromIndex()
                ->help('Link to prescription PDF document'),

            Textarea::make('Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional notes about the prescription'),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),

            HasMany::make('Prescription Items'),
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
            new \App\Nova\Filters\PrescriptionStatusFilter,
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
            new \App\Nova\Lenses\ActivePrescriptions,
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
            new \App\Nova\Actions\DispensePrescriptions,
            new \App\Nova\Actions\RefillPrescriptions,
        ];
    }
}
