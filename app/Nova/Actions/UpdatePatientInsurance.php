<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class UpdatePatientInsurance extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Update Insurance Information';
    public $confirmButtonText = 'Update Insurance';
    public $confirmText = 'Are you sure you want to update insurance information?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $updatedCount = 0;
        $insuranceProvider = $fields->get('insurance_provider');
        $policyNumber = $fields->get('policy_number');
        $groupNumber = $fields->get('group_number');
        $coverageType = $fields->get('coverage_type');
        $notes = $fields->get('insurance_notes');

        foreach ($models as $patient) {
            // Create or update insurance record
            $patient->insurance()->updateOrCreate(
                ['patient_id' => $patient->id],
                [
                    'insurance_provider' => $insuranceProvider,
                    'policy_number' => $policyNumber,
                    'group_number' => $groupNumber,
                    'coverage_type' => $coverageType,
                    'notes' => $notes,
                    'is_primary' => true,
                ]
            );
            $updatedCount++;
        }

        if ($updatedCount > 0) {
            return Action::message("Successfully updated insurance information for {$updatedCount} patients");
        } else {
            return Action::danger("No patients found to update insurance for");
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
            Text::make('Insurance Provider')
                ->rules('required', 'string', 'max:255')
                ->help('Enter the insurance provider name'),

            Text::make('Policy Number')
                ->rules('required', 'string', 'max:255')
                ->help('Enter the policy number'),

            Text::make('Group Number')
                ->nullable()
                ->help('Enter the group number if applicable'),

            Select::make('Coverage Type')
                ->options([
                    'primary' => 'Primary',
                    'secondary' => 'Secondary',
                    'tertiary' => 'Tertiary',
                ])
                ->rules('required')
                ->help('Select the coverage type'),

            Textarea::make('Insurance Notes')
                ->nullable()
                ->help('Optional notes about the insurance coverage'),
        ];
    }
}
