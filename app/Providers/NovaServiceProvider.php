<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Features;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Customize Nova branding for medical clinic
        Nova::name('MediNext Admin');

        // Set initial path to dashboard
        Nova::initialPath('/dashboards/main');

        // Enable breadcrumbs for better navigation
        Nova::withBreadcrumbs();

        // Customize Nova main menu
        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::dashboard(\App\Nova\Dashboards\Main::class)->icon('chart-bar'),

                MenuSection::make('User Management', [
                    MenuItem::resource(\App\Nova\User::class),
                    MenuItem::resource(\App\Nova\Role::class),
                    MenuItem::resource(\App\Nova\Permission::class),
                    MenuItem::resource(\App\Nova\UserClinicRole::class),
                ])->icon('users')->collapsable(),

                MenuSection::make('Clinic Management', [
                    MenuItem::resource(\App\Nova\Clinic::class),
                    MenuItem::resource(\App\Nova\Room::class),
                ])->icon('building-office')->collapsable(),

                MenuSection::make('Medical Staff', [
                    MenuItem::resource(\App\Nova\Doctor::class),
                    MenuItem::resource(\App\Nova\Medrep::class),
                    MenuItem::resource(\App\Nova\MedrepVisit::class),
                ])->icon('user-group')->collapsable(),

                MenuSection::make('Patient Management', [
                    MenuItem::resource(\App\Nova\Patient::class),
                    MenuItem::resource(\App\Nova\Encounter::class),
                ])->icon('heart')->collapsable(),

                MenuSection::make('Clinical Services', [
                    MenuItem::resource(\App\Nova\Appointment::class),
                    MenuItem::resource(\App\Nova\Prescription::class),
                    MenuItem::resource(\App\Nova\PrescriptionItem::class),
                    MenuItem::resource(\App\Nova\LabResult::class),
                ])->icon('document-text')->collapsable(),

                MenuSection::make('System Management', [
                    MenuItem::resource(\App\Nova\Setting::class),
                    MenuItem::resource(\App\Nova\FileAsset::class),
                    MenuItem::resource(\App\Nova\ActivityLog::class)
                ])->icon('cog')->collapsable(),
            ];
        });
    }

    /**
     * Register the configurations for Laravel Fortify.
     */
    protected function fortify(): void
    {
        Nova::fortify()
            ->features([
                Features::updatePasswords(),
                // Features::emailVerification(),
                // Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
            ])
            ->register();
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes(default: true)
            ->withPasswordResetRoutes()
            ->withoutEmailVerificationRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewNova', function (User $user) {
            return in_array($user->email, [
                'rebucasrandy1986@gmail.com',
                // Add other admin emails here
            ]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Tool>
     */
    public function tools(): array
    {
        return [];
    }

    /**
     * Get the resources that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Resource>
     */
    protected function resources(): array
    {
        return [
            // Dashboard
            \App\Nova\Dashboards\Main::class,

            // User Management Group
            \App\Nova\User::class,
            \App\Nova\Role::class,
            \App\Nova\Permission::class,
            \App\Nova\UserClinicRole::class,

            // Clinic Management Group
            \App\Nova\Clinic::class,
            \App\Nova\Room::class,

            // Medical Staff Group
            \App\Nova\Doctor::class,
            \App\Nova\Medrep::class,
            \App\Nova\MedrepVisit::class,

            // Patient Management Group
            \App\Nova\Patient::class,
            \App\Nova\Encounter::class,

            // Clinical Services Group
            \App\Nova\Appointment::class,
            \App\Nova\Prescription::class,
            \App\Nova\PrescriptionItem::class,
            \App\Nova\LabResult::class,

            // System Management Group
            \App\Nova\Setting::class,
            \App\Nova\FileAsset::class,
            \App\Nova\ActivityLog::class
        ];
    }



    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        //
    }
}
