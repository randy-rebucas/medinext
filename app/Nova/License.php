<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Progress;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class License extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\License>
     */
    public static $model = \App\Models\License::class;

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
        'id', 'license_key', 'name', 'customer_name', 'customer_email', 'customer_company',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Licenses';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'License';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'licenses';
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

            Heading::make('License Information'),

            Text::make('License Key')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Unique license key for this license')
                ->copyable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Display name for this license'),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex()
                ->help('Description of this license'),

            Select::make('License Type')
                ->options([
                    'standard' => 'Standard',
                    'premium' => 'Premium',
                    'enterprise' => 'Enterprise',
                ])
                ->sortable()
                ->rules('required')
                ->help('Type of license'),

            Badge::make('Status')
                ->map([
                    'active' => 'success',
                    'expired' => 'danger',
                    'suspended' => 'warning',
                    'revoked' => 'danger',
                ])
                ->sortable(),

            Heading::make('Customer Information'),

            Text::make('Customer Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Name of the customer'),

            Text::make('Customer Email')
                ->sortable()
                ->rules('required', 'email', 'max:255')
                ->help('Customer email address'),

            Text::make('Customer Company')
                ->sortable()
                ->nullable()
                ->hideFromIndex()
                ->help('Customer company name'),

            Text::make('Customer Phone')
                ->nullable()
                ->hideFromIndex()
                ->help('Customer phone number'),

            Heading::make('License Limits'),

            Number::make('Max Users')
                ->sortable()
                ->rules('required', 'integer', 'min:1')
                ->help('Maximum number of users allowed'),

            Number::make('Max Clinics')
                ->sortable()
                ->rules('required', 'integer', 'min:1')
                ->help('Maximum number of clinics allowed'),

            Number::make('Max Patients')
                ->sortable()
                ->rules('required', 'integer', 'min:1')
                ->help('Maximum number of patients allowed'),

            Number::make('Max Appointments Per Month')
                ->sortable()
                ->rules('required', 'integer', 'min:1')
                ->help('Maximum appointments per month'),

            KeyValue::make('Features')
                ->nullable()
                ->hideFromIndex()
                ->help('Enabled features for this license'),

            Heading::make('Validity Period'),

            Date::make('Starts At')
                ->sortable()
                ->rules('required', 'date')
                ->help('License start date'),

            Date::make('Expires At')
                ->sortable()
                ->rules('required', 'date', 'after:starts_at')
                ->help('License expiration date'),

            Number::make('Grace Period Days')
                ->sortable()
                ->rules('required', 'integer', 'min:0')
                ->help('Grace period in days after expiration'),

            Heading::make('Usage Tracking'),

            Progress::make('Users Usage', function () {
                return $this->getUsagePercentage('users');
            })->onlyOnDetail(),

            Progress::make('Clinics Usage', function () {
                return $this->getUsagePercentage('clinics');
            })->onlyOnDetail(),

            Progress::make('Patients Usage', function () {
                return $this->getUsagePercentage('patients');
            })->onlyOnDetail(),

            Progress::make('Appointments Usage', function () {
                return $this->getUsagePercentage('appointments');
            })->onlyOnDetail(),

            Number::make('Current Users')
                ->sortable()
                ->hideFromIndex()
                ->help('Current number of users'),

            Number::make('Current Clinics')
                ->sortable()
                ->hideFromIndex()
                ->help('Current number of clinics'),

            Number::make('Current Patients')
                ->sortable()
                ->hideFromIndex()
                ->help('Current number of patients'),

            Number::make('Appointments This Month')
                ->sortable()
                ->hideFromIndex()
                ->help('Appointments created this month'),

            Heading::make('Server Information'),

            Text::make('Server Domain')
                ->nullable()
                ->hideFromIndex()
                ->help('Server domain where license is activated'),

            Text::make('Server IP')
                ->nullable()
                ->hideFromIndex()
                ->help('Server IP address'),

            Text::make('Server Fingerprint')
                ->nullable()
                ->hideFromIndex()
                ->help('Server fingerprint for security'),

            Heading::make('Billing Information'),

            Boolean::make('Auto Renew')
                ->sortable()
                ->help('Whether license auto-renews'),

            Currency::make('Monthly Fee')
                ->nullable()
                ->sortable()
                ->help('Monthly fee for this license'),

            Select::make('Billing Cycle')
                ->options([
                    'monthly' => 'Monthly',
                    'yearly' => 'Yearly',
                    'lifetime' => 'Lifetime',
                ])
                ->sortable()
                ->help('Billing cycle for this license'),

            Date::make('Last Payment Date')
                ->nullable()
                ->hideFromIndex()
                ->help('Date of last payment'),

            Date::make('Next Payment Date')
                ->nullable()
                ->hideFromIndex()
                ->help('Date of next payment'),

            Heading::make('Activation & Security'),

            Text::make('Activation Code')
                ->nullable()
                ->hideFromIndex()
                ->help('Activation code for this license')
                ->copyable(),

            DateTime::make('Activated At')
                ->nullable()
                ->hideFromIndex()
                ->help('When license was activated'),

            DateTime::make('Last Validated At')
                ->nullable()
                ->hideFromIndex()
                ->help('Last time license was validated'),

            Number::make('Validation Attempts')
                ->sortable()
                ->hideFromIndex()
                ->help('Number of validation attempts'),

            DateTime::make('Last Validation Attempt')
                ->nullable()
                ->hideFromIndex()
                ->help('Last validation attempt timestamp'),

            Heading::make('Support Information'),

            Select::make('Support Level')
                ->options([
                    'standard' => 'Standard',
                    'premium' => 'Premium',
                    'enterprise' => 'Enterprise',
                ])
                ->sortable()
                ->help('Support level for this license'),

            Textarea::make('Support Notes')
                ->nullable()
                ->hideFromIndex()
                ->help('Support notes for this license'),

            Text::make('Assigned Support Agent')
                ->nullable()
                ->hideFromIndex()
                ->help('Assigned support agent'),

            Heading::make('Audit Trail'),

            Code::make('Audit Log')
                ->json()
                ->nullable()
                ->hideFromIndex()
                ->help('Audit log of license changes'),

            Text::make('Created By')
                ->nullable()
                ->hideFromIndex()
                ->help('User who created this license'),

            Text::make('Updated By')
                ->nullable()
                ->hideFromIndex()
                ->help('User who last updated this license'),

            Heading::make('Timestamps'),

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