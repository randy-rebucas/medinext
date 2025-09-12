<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class ConfirmAppointments extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Confirm Appointments';
    public $confirmButtonText = 'Confirm Selected';
    public $confirmText = 'Are you sure you want to confirm these appointments?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $confirmedCount = 0;
        $notes = $fields->get('confirmation_notes');

        foreach ($models as $appointment) {
            if ($appointment->status === 'scheduled') {
                $appointment->update([
                    'status' => 'confirmed',
                    'notes' => $appointment->notes . ($notes ? "\nConfirmation: " . $notes : ''),
                ]);
                $confirmedCount++;
            }
        }

        if ($confirmedCount > 0) {
            return Action::message("Successfully confirmed {$confirmedCount} appointments");
        } else {
            return Action::danger("No scheduled appointments found to confirm");
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
            Textarea::make('Confirmation Notes')
                ->nullable()
                ->help('Optional notes to add to the appointment confirmation'),
        ];
    }

}
