<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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

        // Debug: Log that NovaServiceProvider is booting
        Log::info('NovaServiceProvider is booting');

        // Customize Nova branding for medical clinic
        Nova::name('MediNext Admin');

        // Set initial path to dashboard
        Nova::initialPath('/dashboards/main');

        // Enable breadcrumbs for better navigation
        Nova::withBreadcrumbs();
        // Organize resources into logical menu groups
        Nova::resources([

            // User Management Group
            \App\Nova\User::class,
            \App\Nova\Role::class,
            \App\Nova\Permission::class,
            \App\Nova\UserClinicRole::class,

            // Clinic Management Group
            \App\Nova\Clinic::class,
            \App\Nova\Room::class,
            \App\Nova\Queue::class,
            \App\Nova\QueuePatient::class,

            // Medical Staff Group
            \App\Nova\Doctor::class,
            \App\Nova\Medrep::class,
            \App\Nova\MedrepVisit::class,

            // Patient Management Group
            \App\Nova\Patient::class,
            \App\Nova\Encounter::class,
            \App\Nova\Insurance::class,

            // Clinical Services Group
            \App\Nova\Appointment::class,
            \App\Nova\Prescription::class,
            \App\Nova\PrescriptionItem::class,
            \App\Nova\LabResult::class,

            // Billing Group
            \App\Nova\Bill::class,
            \App\Nova\BillItem::class,

            // System Management Group
            \App\Nova\Setting::class,
            \App\Nova\License::class,
            \App\Nova\FileAsset::class,
            \App\Nova\ActivityLog::class,
            \App\Nova\Notification::class,
        ]);

        // Customize Nova main menu
        Nova::mainMenu(function () {
            return [

                MenuSection::dashboard(\App\Nova\Dashboards\Main::class)->icon('chart-bar'),

                // User Management Group
                MenuSection::make('User Management', [
                    MenuItem::resource(\App\Nova\User::class),
                    MenuItem::resource(\App\Nova\Role::class),
                    MenuItem::resource(\App\Nova\Permission::class),
                    MenuItem::resource(\App\Nova\UserClinicRole::class),
                ])->icon('users')->collapsable(),

                // Clinic Management Group
                MenuSection::make('Clinic Management', [
                    MenuItem::resource(\App\Nova\Clinic::class),
                    MenuItem::resource(\App\Nova\Room::class),
                    MenuItem::resource(\App\Nova\Queue::class),
                    MenuItem::resource(\App\Nova\QueuePatient::class),
                ])->icon('building-office')->collapsable(),

                // Medical Staff Group
                MenuSection::make('Medical Staff', [
                    MenuItem::resource(\App\Nova\Doctor::class),
                    MenuItem::resource(\App\Nova\Medrep::class),
                    MenuItem::resource(\App\Nova\MedrepVisit::class),
                ])->icon('user-group')->collapsable(),

                // Patient Management Group
                MenuSection::make('Patient Management', [
                    MenuItem::resource(\App\Nova\Patient::class),
                    MenuItem::resource(\App\Nova\Encounter::class),
                    MenuItem::resource(\App\Nova\Insurance::class),
                ])->icon('heart')->collapsable(),

                // Clinical Services Group
                MenuSection::make('Clinical Services', [
                    MenuItem::resource(\App\Nova\Appointment::class),
                    MenuItem::resource(\App\Nova\Prescription::class),
                    MenuItem::resource(\App\Nova\PrescriptionItem::class),
                    MenuItem::resource(\App\Nova\LabResult::class),
                ])->icon('document-text')->collapsable(),

                // Billing Group
                MenuSection::make('Billing', [
                    MenuItem::resource(\App\Nova\Bill::class),
                    MenuItem::resource(\App\Nova\BillItem::class),
                ])->icon('currency-dollar')->collapsable(),

                // System Management Group
                MenuSection::make('System Management', [
                    MenuItem::resource(\App\Nova\Setting::class),
                    MenuItem::resource(\App\Nova\License::class),
                    MenuItem::resource(\App\Nova\FileAsset::class),
                    MenuItem::resource(\App\Nova\ActivityLog::class),
                    MenuItem::resource(\App\Nova\Notification::class)
                ])->icon('cog')->collapsable(),
            ];
        });

        Log::info('Nova resources organized into menu groups');
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
            ->withAuthenticationRoutes(default: false)
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
            // return in_array($user->email, [
            //     'rebucasrandy1986@gmail.com',
            //     // Add other admin emails here
            // ]);

            // Allow access if user is active and has admin or doctor role
            if (!$user->is_active) {
                return false;
            }

            // Check if user has admin role in any clinic
            $hasAdminRole = $user->userClinicRoles()
                ->whereHas('role', function ($query) {
                    $query->whereIn('name', ['admin', 'superadmin']);
                })
                ->exists();

            // Check if user has doctor role in any clinic
            $hasDoctorRole = $user->userClinicRoles()
                ->whereHas('role', function ($query) {
                    $query->where('name', 'doctor');
                })
                ->exists();

            // Check if user has receptionist role in any clinic
            $hasReceptionistRole = $user->userClinicRoles()
                ->whereHas('role', function ($query) {
                    $query->where('name', 'receptionist');
                })
                ->exists();

            // Allow access for admin, doctor, and receptionist roles
            return $hasAdminRole || $hasDoctorRole || $hasReceptionistRole;
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
     * Get the resources that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Resource>
     */
    protected function resources(): array
    {
        // Debug: Log that resources method is being called
        Log::info('NovaServiceProvider resources method called');

        $resources = [
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
            \App\Nova\Queue::class,
            \App\Nova\QueuePatient::class,

            // Medical Staff Group
            \App\Nova\Doctor::class,
            \App\Nova\Medrep::class,
            \App\Nova\MedrepVisit::class,

            // Patient Management Group
            \App\Nova\Patient::class,
            \App\Nova\Encounter::class,
            \App\Nova\Insurance::class,

            // Clinical Services Group
            \App\Nova\Appointment::class,
            \App\Nova\Prescription::class,
            \App\Nova\PrescriptionItem::class,
            \App\Nova\LabResult::class,

            // Billing Group
            \App\Nova\Bill::class,
            \App\Nova\BillItem::class,

            // System Management Group
            \App\Nova\Setting::class,
            \App\Nova\License::class,
            \App\Nova\FileAsset::class,
            \App\Nova\ActivityLog::class,
            \App\Nova\Notification::class
        ];
        // Debug: Log the resources being returned
        Log::info('NovaServiceProvider returning resources', ['count' => count($resources)]);

        return $resources;
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
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        //
    }
}
