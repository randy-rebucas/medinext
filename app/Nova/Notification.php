<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;

class Notification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Notification>
     */
    public static $model = \App\Models\Notification::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title', 'message', 'type'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Notifications';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Notification';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'notifications';
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
        return $query->with(['user', 'createdBy']);
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
        return $query->with(['user', 'createdBy', 'notifiable']);
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
                ->searchable()
                ->sortable()
                ->rules('required', 'exists:users,id'),

            Select::make('Type')
                ->options([
                    'info' => 'Info',
                    'success' => 'Success',
                    'warning' => 'Warning',
                    'error' => 'Error',
                    'appointment' => 'Appointment',
                    'prescription' => 'Prescription',
                    'lab_result' => 'Lab Result',
                    'billing' => 'Billing',
                    'system' => 'System',
                    'reminder' => 'Reminder'
                ])
                ->sortable()
                ->rules('required'),

            Text::make('Title')
                ->sortable()
                ->rules('required', 'max:255'),

            Textarea::make('Message')
                ->sortable()
                ->rules('required')
                ->hideFromIndex(),

            KeyValue::make('Data')
                ->nullable()
                ->hideFromIndex()
                ->help('Additional data for the notification'),

            DateTime::make('Read At', 'read_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms(),

            DateTime::make('Sent At', 'sent_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms(),

            Select::make('Delivery Method', 'delivery_method')
                ->options([
                    'database' => 'Database',
                    'email' => 'Email',
                    'sms' => 'SMS',
                    'push' => 'Push Notification',
                    'in_app' => 'In-App'
                ])
                ->sortable()
                ->rules('required'),

            Select::make('Delivery Status', 'delivery_status')
                ->options([
                    'pending' => 'Pending',
                    'sent' => 'Sent',
                    'delivered' => 'Delivered',
                    'failed' => 'Failed',
                    'read' => 'Read'
                ])
                ->sortable()
                ->rules('required'),

            Select::make('Priority')
                ->options([
                    'low' => 'Low',
                    'normal' => 'Normal',
                    'high' => 'High',
                    'urgent' => 'Urgent'
                ])
                ->sortable()
                ->rules('required'),

            DateTime::make('Expires At', 'expires_at')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'date', 'after:now'),

            BelongsTo::make('Created By', 'createdBy', User::class)
                ->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Text::make('User Name', function () {
                return $this->user ? $this->user->name : 'N/A';
            })->onlyOnIndex(),

            \Laravel\Nova\Fields\Text::make('Formatted Message', function () {
                return $this->formatted_message;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Is Read', function () {
                return $this->is_read;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Is Expired', function () {
                return $this->is_expired;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Is Sent', function () {
                return $this->is_sent;
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Badge::make('Type')
                ->map([
                    'info' => 'info',
                    'success' => 'success',
                    'warning' => 'warning',
                    'error' => 'danger',
                    'appointment' => 'info',
                    'prescription' => 'success',
                    'lab_result' => 'info',
                    'billing' => 'warning',
                    'system' => 'info',
                    'reminder' => 'warning',
                ]),

            \Laravel\Nova\Fields\Badge::make('Priority')
                ->map([
                    'low' => 'info',
                    'normal' => 'success',
                    'high' => 'warning',
                    'urgent' => 'danger',
                ]),

            \Laravel\Nova\Fields\Badge::make('Delivery Status')
                ->map([
                    'pending' => 'warning',
                    'sent' => 'info',
                    'delivered' => 'success',
                    'failed' => 'danger',
                    'read' => 'success',
                ]),

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
