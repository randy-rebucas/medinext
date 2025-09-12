<?php

namespace App\Nova\Actions;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Http\Requests\NovaRequest;

class AutoFillLicenseFields extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Auto Fill License Fields';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $licenseType = $fields->license_type;
        $overwriteExisting = $fields->overwrite_existing ?? false;
        $customDuration = $fields->custom_duration ?? null;
        $customGracePeriod = $fields->custom_grace_period ?? null;

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($models as $license) {
            // Get update data from the model method
            $updateData = $license->autoFillFromType($licenseType, $overwriteExisting);
            
            // Apply custom duration if provided
            if ($customDuration && isset($updateData['expires_at'])) {
                $updateData['expires_at'] = now()->addMonths($customDuration);
            }
            
            // Apply custom grace period if provided
            if ($customGracePeriod && isset($updateData['grace_period_days'])) {
                $updateData['grace_period_days'] = $customGracePeriod;
            }

            // Apply updates if any
            if (!empty($updateData)) {
                $license->update($updateData);
                
                $license->addAuditLog('License fields auto-filled', [
                    'license_type' => $licenseType,
                    'overwrite_existing' => $overwriteExisting,
                    'updated_fields' => array_keys($updateData),
                    'updated_by' => 'nova_admin'
                ]);
                
                $updatedCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($updatedCount > 0) {
            return Action::message("Successfully auto-filled {$updatedCount} license(s). " . 
                ($skippedCount > 0 ? "Skipped {$skippedCount} license(s) (no changes needed)." : ""));
        } else {
            return Action::message("No licenses were updated. All selected licenses already have the required fields filled.");
        }
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
            Select::make('License Type')
                ->options([
                    'standard' => 'Standard',
                    'premium' => 'Premium',
                    'enterprise' => 'Enterprise',
                ])
                ->rules('required')
                ->help('Select the license type to auto-fill fields'),

            Boolean::make('Overwrite Existing Fields')
                ->default(false)
                ->help('If enabled, will overwrite existing field values. If disabled, will only fill empty fields.'),

            Number::make('Custom Duration (Months)')
                ->nullable()
                ->min(1)
                ->max(120)
                ->help('Custom validity duration in months (optional, will use default if not specified)'),

            Number::make('Custom Grace Period (Days)')
                ->nullable()
                ->min(0)
                ->max(365)
                ->help('Custom grace period in days (optional, will use default if not specified)'),
        ];
    }


    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function authorizedToSee(\Illuminate\Http\Request $request)
    {
        return $request->user() && $request->user()->can('manage-licenses');
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function authorizedToRun(\Illuminate\Http\Request $request, $model)
    {
        return $request->user() && $request->user()->can('manage-licenses');
    }
}
