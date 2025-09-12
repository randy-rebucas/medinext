<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Auth\PasswordValidationRules;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    use PasswordValidationRules;

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
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Users';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'User';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'users';
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
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('Phone')
                ->sortable()
                ->nullable()
                ->rules('nullable', 'max:20'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules($this->passwordRules())
                ->updateRules($this->optionalPasswordRules()),

            Boolean::make('Is Active', 'is_active')
                ->sortable(),

            Text::make('License Key', 'license_key')
                ->sortable()
                ->nullable()
                ->hideFromIndex()
                ->rules('nullable', 'max:255'),

            Boolean::make('Is Trial User', 'is_trial_user')
                ->sortable()
                ->hideFromIndex(),

            Boolean::make('Has Activated License', 'has_activated_license')
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Trial Started At', 'trial_started_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Trial Ends At', 'trial_ends_at')
                ->sortable()
                ->nullable()
                ->exceptOnForms()
                ->hideFromIndex(),

            BelongsTo::make('License')
                ->nullable()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Text::make('Access Status', function () {
                $status = $this->getAccessStatus();
                return $status['message'];
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Boolean::make('Has Valid Access', function () {
                return $this->hasValidAccess();
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Number::make('Trial Days Remaining', function () {
                return $this->getTrialDaysRemaining();
            })->exceptOnForms()
                ->hideFromIndex(),

            \Laravel\Nova\Fields\Badge::make('Status', function () {
                if ($this->has_activated_license) {
                    return 'Licensed';
                } elseif ($this->isOnTrial()) {
                    return 'Trial';
                } elseif ($this->isTrialExpired()) {
                    return 'Expired';
                } else {
                    return 'Inactive';
                }
            })->map([
                'Licensed' => 'success',
                'Trial' => 'info',
                'Expired' => 'danger',
                'Inactive' => 'warning',
            ]),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),

            HasMany::make('User Clinic Roles', 'userClinicRoles', UserClinicRole::class)
                ->hideFromIndex(),

            HasMany::make('Doctors')
                ->hideFromIndex(),

            HasMany::make('Notifications')
                ->hideFromIndex(),

            HasMany::make('Created Bills', 'createdBills', Bill::class)
                ->hideFromIndex(),

            HasMany::make('Updated Bills', 'updatedBills', Bill::class)
                ->hideFromIndex(),

            HasMany::make('Created Insurance', 'createdInsurance', Insurance::class)
                ->hideFromIndex(),

            HasMany::make('Updated Insurance', 'updatedInsurance', Insurance::class)
                ->hideFromIndex(),

            HasMany::make('Created Queues', 'createdQueues', Queue::class)
                ->hideFromIndex(),

            HasMany::make('Updated Queues', 'updatedQueues', Queue::class)
                ->hideFromIndex(),

            HasMany::make('Created Notifications', 'createdNotifications', Notification::class)
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
            new \App\Nova\Filters\StatusFilter,
            new \App\Nova\Filters\DateRangeFilter,
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
            new \App\Nova\Actions\ExportData,
            new \App\Nova\Actions\BulkUpdate,
        ];
    }
}
