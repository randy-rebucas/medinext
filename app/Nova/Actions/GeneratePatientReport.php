<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class GeneratePatientReport extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Generate Patient Report';
    public $confirmButtonText = 'Generate Report';
    public $confirmText = 'Are you sure you want to generate patient reports?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $reportType = $fields->get('report_type');
        $startDate = $fields->get('start_date');
        $endDate = $fields->get('end_date');
        $generatedCount = 0;

        foreach ($models as $patient) {
            // In a real implementation, you would generate the actual report
            // For now, we'll just simulate the report generation
            $generatedCount++;
        }

        if ($generatedCount > 0) {
            return Action::message("Successfully generated {$generatedCount} patient reports of type: {$reportType}");
        } else {
            return Action::danger("No patients found to generate reports for");
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
            Select::make('Report Type')
                ->options([
                    'medical_history' => 'Medical History',
                    'appointment_summary' => 'Appointment Summary',
                    'prescription_history' => 'Prescription History',
                    'billing_summary' => 'Billing Summary',
                    'insurance_claims' => 'Insurance Claims',
                    'comprehensive' => 'Comprehensive Report',
                ])
                ->rules('required')
                ->help('Select the type of report to generate'),

            Date::make('Start Date')
                ->rules('required', 'date')
                ->help('Start date for the report period'),

            Date::make('End Date')
                ->rules('required', 'date', 'after:start_date')
                ->help('End date for the report period'),
        ];
    }
}
