<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class CancelAppointments extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Cancel Appointments';
    public $confirmButtonText = 'Cancel Selected';
    public $confirmText = 'Are you sure you want to cancel these appointments?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $cancelledCount = 0;
        $reason = $fields->get('cancellation_reason');
        $notes = $fields->get('cancellation_notes');

        foreach ($models as $appointment) {
            if (in_array($appointment->status, ['scheduled', 'confirmed'])) {
                $appointment->update([
                    'status' => 'cancelled',
                    'notes' => $appointment->notes . "\nCancelled: " . $reason . ($notes ? " - " . $notes : ''),
                ]);
                $cancelledCount++;
            }
        }

        if ($cancelledCount > 0) {
            return Action::message("Successfully cancelled {$cancelledCount} appointments");
        } else {
            return Action::danger("No cancellable appointments found");
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
            Select::make('Cancellation Reason')
                ->options([
                    'patient_request' => 'Patient Request',
                    'doctor_unavailable' => 'Doctor Unavailable',
                    'emergency' => 'Emergency',
                    'weather' => 'Weather',
                    'technical_issue' => 'Technical Issue',
                    'other' => 'Other',
                ])
                ->rules('required')
                ->help('Select the reason for cancellation'),

            Textarea::make('Cancellation Notes')
                ->nullable()
                ->help('Additional notes about the cancellation'),
        ];
    }

}
