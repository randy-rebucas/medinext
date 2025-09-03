<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class MedrepVisit extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\MedrepVisit>
     */
    public static $model = \App\Models\MedrepVisit::class;

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
        'id', 'purpose', 'clinic_id', 'medrep_id', 'doctor_id'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Medical Representative Visits';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Medical Representative Visit';
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
                ->rules('required', 'exists:clinics,id')
                ->help('Clinic where the visit occurred'),

            BelongsTo::make('Medical Representative', 'medrep', Medrep::class)
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:medreps,id')
                ->help('Medical representative making the visit'),

            BelongsTo::make('Doctor')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:doctors,id')
                ->help('Doctor being visited'),

            DateTime::make('Start Time', 'start_at')
                ->sortable()
                ->rules('required', 'date')
                ->help('When the visit started'),

            DateTime::make('End Time', 'end_at')
                ->sortable()
                ->rules('required', 'date', 'after:start_at')
                ->help('When the visit ended'),

            Select::make('Purpose')
                ->options([
                    'product_introduction' => 'Product Introduction',
                    'product_demonstration' => 'Product Demonstration',
                    'sample_distribution' => 'Sample Distribution',
                    'order_collection' => 'Order Collection',
                    'relationship_building' => 'Relationship Building',
                    'training' => 'Training',
                    'feedback_collection' => 'Feedback Collection',
                    'market_research' => 'Market Research',
                    'other' => 'Other'
                ])
                ->sortable()
                ->rules('required')
                ->help('Purpose of the visit'),

            Textarea::make('Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional notes about the visit'),

            DateTime::make('Created At')
                ->sortable()
                ->hideFromForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->hideFromForms(),
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
