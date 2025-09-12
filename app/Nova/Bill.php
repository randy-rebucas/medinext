<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
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

class Bill extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Bill>
     */
    public static $model = \App\Models\Bill::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'bill_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'bill_number', 'status', 'payment_method'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Bills';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Bill';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'bills';
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
        return $query->with(['patient', 'encounter', 'clinic', 'createdBy', 'updatedBy']);
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
        return $query->with(['patient', 'encounter', 'clinic', 'items', 'createdBy', 'updatedBy']);
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

            Text::make('Bill Number', 'bill_number')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Auto-generated bill number'),

            BelongsTo::make('Patient')
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:patients,id'),

            BelongsTo::make('Encounter')
                ->searchable()
                ->sortable()
                ->nullable()
                ->rules('nullable', 'exists:encounters,id'),

            BelongsTo::make('Clinic')
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:clinics,id'),

            Date::make('Bill Date', 'bill_date')
                ->sortable()
                ->rules('required', 'date'),

            Date::make('Due Date', 'due_date')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'date', 'after_or_equal:bill_date'),

            Number::make('Subtotal')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Subtotal before tax and discounts'),

            Number::make('Tax Amount', 'tax_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Tax amount'),

            Number::make('Discount Amount', 'discount_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Discount amount'),

            Number::make('Total Amount', 'total_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Total amount after tax and discounts'),

            Number::make('Paid Amount', 'paid_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Amount already paid'),

            Number::make('Balance Amount', 'balance_amount')
                ->sortable()
                ->step(0.01)
                ->rules('numeric', 'min:0')
                ->help('Remaining balance'),

            Select::make('Status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'partial' => 'Partial',
                    'overdue' => 'Overdue',
                    'cancelled' => 'Cancelled'
                ])
                ->sortable()
                ->rules('required'),

            Select::make('Payment Method', 'payment_method')
                ->options([
                    'cash' => 'Cash',
                    'credit_card' => 'Credit Card',
                    'debit_card' => 'Debit Card',
                    'check' => 'Check',
                    'bank_transfer' => 'Bank Transfer',
                    'insurance' => 'Insurance',
                    'other' => 'Other'
                ])
                ->sortable()
                ->nullable()
                ->rules('nullable'),

            Text::make('Payment Reference', 'payment_reference')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'max:255')
                ->help('Payment reference number or transaction ID'),

            Textarea::make('Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional notes about the bill'),

            BelongsTo::make('Created By', 'createdBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            BelongsTo::make('Updated By', 'updatedBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Text::make('Patient Name', function () {
                return $this->patient ? $this->patient->full_name : 'N/A';
            })->onlyOnIndex(),

            \Laravel\Nova\Fields\Number::make('Balance', function () {
                return $this->balance;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Is Paid', function () {
                return $this->is_paid;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Is Overdue', function () {
                return $this->is_overdue;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Badge::make('Status')
                ->map([
                    'pending' => 'warning',
                    'paid' => 'success',
                    'partial' => 'info',
                    'overdue' => 'danger',
                    'cancelled' => 'danger',
                ]),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            HasMany::make('Items', 'items', BillItem::class)
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
            new \App\Nova\Filters\BillStatusFilter,
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
            new \App\Nova\Lenses\OverdueBills,
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
            new \App\Nova\Actions\MarkBillAsPaid,
            new \App\Nova\Actions\SendBillReminder,
        ];
    }
}
