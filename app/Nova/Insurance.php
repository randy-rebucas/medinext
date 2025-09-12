<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;

class Insurance extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Insurance>
     */
    public static $model = \App\Models\Insurance::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'insurance_provider';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'insurance_provider', 'policy_number', 'member_id', 'policy_holder_name'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Insurance';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Insurance';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'insurance';
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
        return $query->with(['patient', 'createdBy', 'updatedBy']);
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
        return $query->with(['patient', 'createdBy', 'updatedBy']);
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
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:patients,id'),

            Text::make('Insurance Provider', 'insurance_provider')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Policy Number', 'policy_number')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Group Number', 'group_number')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'max:255'),

            Text::make('Member ID', 'member_id')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Policy Holder Name', 'policy_holder_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make('Policy Holder Relationship', 'policy_holder_relationship')
                ->options([
                    'self' => 'Self',
                    'spouse' => 'Spouse',
                    'child' => 'Child',
                    'parent' => 'Parent',
                    'sibling' => 'Sibling',
                    'other' => 'Other'
                ])
                ->sortable()
                ->rules('required'),

            Select::make('Coverage Type', 'coverage_type')
                ->options([
                    'health' => 'Health',
                    'dental' => 'Dental',
                    'vision' => 'Vision',
                    'mental_health' => 'Mental Health',
                    'pharmacy' => 'Pharmacy',
                    'other' => 'Other'
                ])
                ->sortable()
                ->rules('required'),

            Date::make('Effective Date', 'effective_date')
                ->sortable()
                ->rules('required', 'date'),

            Date::make('Expiration Date', 'expiration_date')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'date', 'after:effective_date'),

            Number::make('Copay Amount', 'copay_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Copay amount in dollars'),

            Number::make('Deductible Amount', 'deductible_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Deductible amount in dollars'),

            Number::make('Coverage Percentage', 'coverage_percentage')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0', 'max:100')
                ->help('Coverage percentage (0-100)'),

            Boolean::make('Is Primary', 'is_primary')
                ->sortable()
                ->help('Primary insurance for this patient'),

            Boolean::make('Is Active', 'is_active')
                ->sortable(),

            Select::make('Verification Status', 'verification_status')
                ->options([
                    'pending' => 'Pending',
                    'verified' => 'Verified',
                    'rejected' => 'Rejected',
                    'expired' => 'Expired'
                ])
                ->sortable()
                ->rules('required'),

            DateTime::make('Verification Date', 'verification_date')
                ->sortable()
                ->nullable()
                ->exceptOnForms(),

            Text::make('Verification Notes', 'verification_notes')
                ->nullable()
                ->hideFromIndex(),

            Text::make('Contact Phone', 'contact_phone')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'max:20'),

            Text::make('Contact Email', 'contact_email')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'email', 'max:255'),

            BelongsTo::make('Created By', 'createdBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            BelongsTo::make('Updated By', 'updatedBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Text::make('Patient Name', function () {
                return $this->patient ? $this->patient->full_name : 'N/A';
            })->onlyOnIndex(),

            \Laravel\Nova\Fields\Text::make('Full Policy Number', function () {
                return $this->full_policy_number;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Badge::make('Verification Status')
                ->map([
                    'pending' => 'warning',
                    'verified' => 'success',
                    'rejected' => 'danger',
                    'expired' => 'danger',
                ]),

            \Laravel\Nova\Fields\Boolean::make('Is Expired', function () {
                return $this->is_expired;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Is Expiring', function () {
                return $this->is_expiring;
            })->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms()
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
