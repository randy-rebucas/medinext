<?php

namespace App\Nova\Actions;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;

class ResetSettingsToDefault extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Reset to Default';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Reset Settings';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Are you sure you want to reset these settings to their default values? This action cannot be undone.';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $resetCount = 0;
        $skipCount = 0;
        $includeSystemSettings = $fields->get('include_system_settings', false);

        foreach ($models as $setting) {
            // Skip system settings unless explicitly included
            if (!$includeSystemSettings && !$setting->isEditable()) {
                $skipCount++;
                continue;
            }

            // Get default value based on setting key
            $defaultValue = $this->getDefaultValue($setting->key);

            if ($defaultValue !== null) {
                $setting->update(['value' => $defaultValue]);
                $resetCount++;
            } else {
                $skipCount++;
            }
        }

        $message = "Reset {$resetCount} settings to default values";
        if ($skipCount > 0) {
            $message .= " ({$skipCount} skipped)";
        }

        return Action::message($message);
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Boolean::make('Include System Settings', 'include_system_settings')
                ->help('Include system settings that are normally protected from editing')
                ->default(false),
        ];
    }

    /**
     * Get default value for a setting key
     *
     * @param string $key
     * @return mixed
     */
    private function getDefaultValue(string $key)
    {
        $defaults = [
            // Clinic Information
            'clinic.name' => 'Your Clinic Name',
            'clinic.phone' => '+63 123 456 7890',
            'clinic.email' => 'info@yourclinic.com',
            'clinic.address' => [
                'street' => '123 Main Street',
                'city' => 'Manila',
                'state' => 'Metro Manila',
                'postal_code' => '1000',
                'country' => 'Philippines'
            ],
            'clinic.website' => 'https://yourclinic.com',
            'clinic.description' => 'Professional healthcare services for your family',

            // Working Hours
            'working_hours.monday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.tuesday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.wednesday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.thursday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.friday' => ['start' => '08:00', 'end' => '17:00', 'closed' => false],
            'working_hours.saturday' => ['start' => '08:00', 'end' => '12:00', 'closed' => false],
            'working_hours.sunday' => ['start' => '00:00', 'end' => '00:00', 'closed' => true],

            // Notifications
            'notifications.email_enabled' => true,
            'notifications.sms_enabled' => false,
            'notifications.appointment_reminder_hours' => 24,
            'notifications.follow_up_days' => 7,

            // Branding
            'branding.primary_color' => '#3B82F6',
            'branding.secondary_color' => '#1E40AF',
            'branding.logo_url' => null,
            'branding.favicon_url' => null,

            // System Settings
            'system.timezone' => 'Asia/Manila',
            'system.date_format' => 'Y-m-d',
            'system.time_format' => 'H:i',
            'system.currency' => 'PHP',
        ];

        return $defaults[$key] ?? null;
    }
}
