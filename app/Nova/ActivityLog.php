<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class ActivityLog extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ActivityLog>
     */
    public static $model = \App\Models\ActivityLog::class;

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
        'id', 'entity', 'action', 'actor_user_id', 'clinic_id'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Activity Logs';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Activity Log';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'activity-logs';
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

            Heading::make('Activity Information'),

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:clinics,id'),

            BelongsTo::make('Actor', 'actor', User::class)
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:users,id')
                ->help('User who performed the action'),

            Text::make('Entity')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Type of entity affected (e.g., User, Patient, Appointment)'),

            Text::make('Entity ID')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('ID of the affected entity'),

            Heading::make('Action Details'),

            Select::make('Action')
                ->options([
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'deleted' => 'Deleted',
                    'viewed' => 'Viewed',
                    'exported' => 'Exported',
                    'imported' => 'Imported',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'scheduled' => 'Scheduled',
                    'cancelled' => 'Cancelled',
                    'completed' => 'Completed',
                    'assigned' => 'Assigned',
                    'unassigned' => 'Unassigned',
                    'logged_in' => 'Logged In',
                    'logged_out' => 'Logged Out',
                    'password_changed' => 'Password Changed',
                    'role_assigned' => 'Role Assigned',
                    'permission_granted' => 'Permission Granted',
                    'file_uploaded' => 'File Uploaded',
                    'file_downloaded' => 'File Downloaded',
                    'appointment_scheduled' => 'Appointment Scheduled',
                    'prescription_issued' => 'Prescription Issued',
                    'lab_result_ordered' => 'Lab Result Ordered',
                    'checkin' => 'Patient Check-in',
                    'checkout' => 'Patient Check-out',
                    'payment_received' => 'Payment Received',
                    'refund_issued' => 'Refund Issued',
                ])
                ->sortable()
                ->rules('required')
                ->help('Type of action performed'),

            Text::make('Action Display Name', function () {
                return $this->action_display_name;
            })->onlyOnDetail(),

            Heading::make('Classification'),

            Badge::make('Category', function () {
                return $this->category;
            })->sortable(),

            Badge::make('Severity', function () {
                return $this->severity;
            })->map([
                'High' => 'danger',
                'Medium' => 'warning',
                'Low' => 'info',
            ])->sortable(),

            Badge::make('Color', function () {
                return $this->color;
            })->map([
                'danger' => 'danger',
                'warning' => 'warning',
                'info' => 'info',
            ])->onlyOnDetail(),

            Heading::make('Timing'),

            DateTime::make('At')
                ->sortable()
                ->rules('required', 'date')
                ->help('When the action occurred'),

            Text::make('Formatted Time', function () {
                return $this->formatted_time;
            })->onlyOnDetail(),

            Text::make('Is Recent', function () {
                return $this->is_recent ? 'Yes' : 'No';
            })->onlyOnDetail(),

            Heading::make('Technical Details'),

            Text::make('IP Address', 'ip')
                ->sortable()
                ->rules('nullable', 'ip')
                ->help('IP address of the user who performed the action'),

            Text::make('Icon', function () {
                return $this->icon;
            })->onlyOnDetail(),

            Heading::make('Metadata'),

            Code::make('Meta')
                ->json()
                ->nullable()
                ->hideFromIndex()
                ->help('Additional metadata about the action (JSON format)'),

            Text::make('Formatted Meta', function () {
                return $this->formatted_meta;
            })->onlyOnDetail(),

            Text::make('Before Hash')
                ->nullable()
                ->hideFromIndex()
                ->help('Hash of the entity state before the action'),

            Text::make('After Hash')
                ->nullable()
                ->hideFromIndex()
                ->help('Hash of the entity state after the action'),

            Heading::make('Activity Summary'),

            Text::make('Description', function () {
                return $this->description;
            })->onlyOnDetail(),

            Text::make('Is Critical', function () {
                return $this->is_critical ? 'Yes' : 'No';
            })->onlyOnDetail(),

            Heading::make('Relationships'),

            Text::make('Related Activities Count', function () {
                return $this->related_activities->count();
            })->onlyOnDetail(),

            Heading::make('Timestamps'),

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
