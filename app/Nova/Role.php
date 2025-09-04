<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
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

class Role extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Role>
     */
    public static $model = \App\Models\Role::class;

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
        'id', 'name', 'description',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Roles';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Role';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'roles';
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
                ->rules('required', 'max:255', 'unique:roles,name,{{resourceId}}')
                ->help('Role identifier (e.g., admin, doctor, patient)'),

            Text::make('Display Name', function () {
                return $this->display_name;
            })->onlyOnDetail(),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex()
                ->help('Detailed description of the role\'s purpose and responsibilities'),

            Heading::make('Role Information'),

            Badge::make('Security Level', function () {
                return $this->security_level;
            })->map([
                'Critical' => 'danger',
                'High' => 'warning',
                'Medium-High' => 'info',
                'Medium' => 'info',
                'Low' => 'success',
            ])->sortable(),

            Text::make('Capabilities', function () {
                return $this->capabilities_description;
            })->onlyOnDetail(),

            Boolean::make('Is System Role', 'is_system_role')
                ->sortable()
                ->help('System roles cannot be deleted or modified')
                ->readonly(function () {
                    return $this->is_system_role;
                }),

            Heading::make('Permissions'),

            BelongsToMany::make('Permissions')
                ->searchable()
                ->showCreateRelationButton()
                ->help('Assign permissions to this role'),

            Code::make('Permissions Config', 'permissions_config')
                ->json()
                ->nullable()
                ->hideFromIndex()
                ->help('JSON configuration for role permissions'),

            Heading::make('Usage Statistics'),

            Text::make('Total Users', function () {
                return $this->usage_statistics['total_users'] ?? 0;
            })->onlyOnDetail(),

            Text::make('Total Clinics', function () {
                return $this->usage_statistics['total_clinics'] ?? 0;
            })->onlyOnDetail(),

            Text::make('Permission Count', function () {
                return $this->usage_statistics['permission_count'] ?? 0;
            })->onlyOnDetail(),

            Heading::make('Validation'),

            Text::make('Minimum Permissions', function () {
                return $this->hasMinimumPermissions() ? 'Valid' : 'Invalid';
            })->onlyOnDetail(),

            Text::make('Permission Validation', function () {
                $errors = $this->validatePermissions();
                return empty($errors) ? 'Valid' : 'Has ' . count($errors) . ' issues';
            })->onlyOnDetail(),

            Heading::make('Relationships'),

            HasMany::make('User Clinic Roles', 'userClinicRoles'),

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
