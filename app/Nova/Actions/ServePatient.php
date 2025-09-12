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

class ServePatient extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Serve Patient';
    public $confirmButtonText = 'Mark as Served';
    public $confirmText = 'Are you sure you want to mark this patient as served?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $servedCount = 0;
        $notes = $fields->get('service_notes');

        foreach ($models as $queuePatient) {
            if (in_array($queuePatient->status, ['waiting', 'called'])) {
                $waitTime = $queuePatient->called_at
                    ? $queuePatient->called_at->diffInMinutes(now())
                    : $queuePatient->created_at->diffInMinutes(now());

                $queuePatient->update([
                    'status' => 'served',
                    'served_at' => now(),
                    'actual_wait_time' => $waitTime,
                    'notes' => $queuePatient->notes . ($notes ? "\nServed: " . $notes : ''),
                ]);
                $servedCount++;
            }
        }

        if ($servedCount > 0) {
            return Action::message("Successfully marked {$servedCount} patients as served");
        } else {
            return Action::danger("No patients found to mark as served");
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
            Textarea::make('Service Notes')
                ->nullable()
                ->help('Optional notes about the service provided'),
        ];
    }

}
