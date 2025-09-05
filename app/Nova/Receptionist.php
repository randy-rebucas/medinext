<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Receptionist extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

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
        'id', 'name', 'email', 'phone',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Staff Management';

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Receptionists';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Receptionist';
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, Builder $query): Builder
    {
        // Only show users who have the receptionist role
        return $query->whereHas('userClinicRoles.role', function ($q) {
            $q->where('name', 'receptionist');
        });
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field|\Laravel\Nova\Panel|\Laravel\Nova\ResourceTool|\Illuminate\Http\Resources\MergeValue>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Gravatar::make()->maxWidth(50),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Full name of the receptionist'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->help('Email address for login and notifications'),

            Text::make('Phone')
                ->sortable()
                ->rules('nullable', 'string', 'max:20')
                ->help('Contact phone number'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'min:8')
                ->updateRules('nullable', 'min:8')
                ->help('Password for system access'),

            Boolean::make('Active', 'is_active')
                ->sortable()
                ->help('Whether the receptionist account is active'),

            DateTime::make('Created At')
                ->exceptOnForms()
                ->sortable(),

            DateTime::make('Updated At')
                ->exceptOnForms()
                ->sortable(),

            // Show clinic assignments
            BelongsToMany::make('Clinics', 'clinics', Clinic::class)
                ->fields(function () {
                    return [
                        Text::make('Role', function () {
                            return 'Receptionist';
                        })->readonly(),
                    ];
                })
                ->help('Clinics where this receptionist is assigned'),

            // Show permissions summary
            Textarea::make('Permissions Summary')
                ->onlyOnDetail()
                ->resolveUsing(function () {
                    $permissions = $this->getPermissionsInClinic(1); // Assuming clinic ID 1 for demo
                    $permissionGroups = [
                        'Patient Management' => array_filter($permissions, fn($p) => str_starts_with($p, 'patient')),
                        'Appointment Management' => array_filter($permissions, fn($p) => str_starts_with($p, 'appointment')),
                        'Schedule Management' => array_filter($permissions, fn($p) => str_starts_with($p, 'schedule')),
                        'Billing' => array_filter($permissions, fn($p) => str_starts_with($p, 'billing')),
                    ];

                    $summary = '';
                    foreach ($permissionGroups as $group => $perms) {
                        if (!empty($perms)) {
                            $summary .= "• {$group}: " . implode(', ', $perms) . "\n";
                        }
                    }

                    return $summary ?: 'No specific permissions assigned';
                })
                ->help('Summary of receptionist permissions'),
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
            new Filters\ReceptionistStatusFilter,
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
            new Lenses\ActiveReceptionists,
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
            new Actions\BulkUpdate,
        ];
    }

}
