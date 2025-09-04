<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
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

class Permission extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Permission>
     */
    public static $model = \App\Models\Permission::class;

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
        'id', 'name', 'slug', 'module', 'action',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Permissions';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Permission';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'permissions';
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
                ->help('Human-readable permission name'),

            Text::make('Slug')
                ->sortable()
                ->rules('required', 'max:255', 'unique:permissions,slug,{{resourceId}}')
                ->help('Unique identifier for the permission (e.g., clinics.manage)'),

            Text::make('Full Permission', function () {
                return $this->full_permission;
            })->onlyOnDetail(),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex()
                ->help('Brief description of what this permission allows'),

            Heading::make('Permission Structure'),

            Select::make('Module')
                ->options([
                    'clinics' => 'Clinics',
                    'doctors' => 'Doctors',
                    'patients' => 'Patients',
                    'appointments' => 'Appointments',
                    'prescriptions' => 'Prescriptions',
                    'medical_records' => 'Medical Records',
                    'users' => 'Users',
                    'roles' => 'Roles',
                    'billing' => 'Billing',
                    'reports' => 'Reports',
                    'settings' => 'Settings',
                    'schedule' => 'Schedule',
                    'products' => 'Products',
                    'meetings' => 'Meetings',
                    'interactions' => 'Interactions',
                    'profile' => 'Profile',
                ])
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Module this permission belongs to'),

            Select::make('Action')
                ->options([
                    'view' => 'View',
                    'create' => 'Create',
                    'edit' => 'Edit',
                    'delete' => 'Delete',
                    'manage' => 'Manage',
                    'export' => 'Export',
                    'download' => 'Download',
                    'checkin' => 'Check-in',
                    'cancel' => 'Cancel',
                ])
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Action this permission allows'),

            Heading::make('Classification'),

            Badge::make('Category', function () {
                return $this->category;
            })->sortable(),

            Badge::make('Risk Level', function () {
                return $this->risk_level;
            })->map([
                'High' => 'danger',
                'Medium' => 'warning',
                'Low' => 'success',
            ])->sortable(),

            Heading::make('Context'),

            Text::make('Contextual Description', function () {
                return $this->contextual_description;
            })->onlyOnDetail(),

            Heading::make('Usage Statistics'),

            Text::make('Total Roles', function () {
                return $this->usage_statistics['total_roles'] ?? 0;
            })->onlyOnDetail(),

            Text::make('Total Users', function () {
                return $this->usage_statistics['total_users'] ?? 0;
            })->onlyOnDetail(),

            Heading::make('Dependencies'),

            Text::make('Dependencies', function () {
                $deps = $this->dependencies;
                return empty($deps) ? 'None' : implode(', ', $deps);
            })->onlyOnDetail(),

            Text::make('Has Dependencies', function () {
                return $this->hasDependencies() ? 'Yes' : 'No';
            })->onlyOnDetail(),

            Heading::make('Relationships'),

            BelongsToMany::make('Roles')
                ->searchable()
                ->showCreateRelationButton(),

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
