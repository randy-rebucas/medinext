<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class PrescriptionItem extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\PrescriptionItem>
     */
    public static $model = \App\Models\PrescriptionItem::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'drug_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'drug_name', 'strength', 'form', 'prescription_id'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Prescription Items';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Prescription Item';
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

            BelongsTo::make('Prescription')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:prescriptions,id')
                ->help('Parent prescription this item belongs to'),

            Text::make('Drug Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Name of the prescribed medication'),

            Text::make('Strength')
                ->sortable()
                ->rules('required', 'max:100')
                ->help('Medication strength (e.g., 500mg, 10mg)'),

            Select::make('Form')
                ->options([
                    'tablet' => 'Tablet',
                    'capsule' => 'Capsule',
                    'liquid' => 'Liquid',
                    'injection' => 'Injection',
                    'cream' => 'Cream',
                    'ointment' => 'Ointment',
                    'drops' => 'Drops',
                    'inhaler' => 'Inhaler',
                    'patch' => 'Patch',
                    'suppository' => 'Suppository',
                    'other' => 'Other'
                ])
                ->sortable()
                ->rules('required')
                ->help('Form of the medication'),

            Text::make('Sig (Instructions)', 'sig')
                ->sortable()
                ->rules('required', 'max:500')
                ->help('Instructions for taking the medication (e.g., Take 1 tablet twice daily)'),

            Number::make('Quantity')
                ->sortable()
                ->rules('required', 'integer', 'min:1')
                ->help('Quantity of medication prescribed'),

            Number::make('Refills')
                ->sortable()
                ->rules('required', 'integer', 'min:0')
                ->help('Number of refills allowed'),

            Textarea::make('Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional notes about this prescription item'),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),
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
