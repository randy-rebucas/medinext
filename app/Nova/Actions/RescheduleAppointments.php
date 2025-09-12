<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class RescheduleAppointments extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Reschedule Appointments';
    public $confirmButtonText = 'Reschedule Selected';
    public $confirmText = 'Are you sure you want to reschedule these appointments?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $rescheduledCount = 0;
        $newStartTime = $fields->get('new_start_time');
        $newEndTime = $fields->get('new_end_time');
        $notes = $fields->get('reschedule_notes');

        foreach ($models as $appointment) {
            if (in_array($appointment->status, ['scheduled', 'confirmed'])) {
                $appointment->update([
                    'start_at' => $newStartTime,
                    'end_at' => $newEndTime,
                    'status' => 'scheduled',
                    'notes' => $appointment->notes . "\nRescheduled from: " . $appointment->start_at . " to: " . $newStartTime . ($notes ? " - " . $notes : ''),
                ]);
                $rescheduledCount++;
            }
        }

        if ($rescheduledCount > 0) {
            return Action::message("Successfully rescheduled {$rescheduledCount} appointments");
        } else {
            return Action::danger("No reschedulable appointments found");
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
            DateTime::make('New Start Time')
                ->rules('required', 'date', 'after:now')
                ->help('Select the new start time for the appointment'),

            DateTime::make('New End Time')
                ->rules('required', 'date', 'after:new_start_time')
                ->help('Select the new end time for the appointment'),

            Textarea::make('Reschedule Notes')
                ->nullable()
                ->help('Optional notes about the rescheduling'),
        ];
    }

}
