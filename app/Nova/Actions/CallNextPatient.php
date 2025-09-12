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

class CallNextPatient extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Call Next Patient';
    public $confirmButtonText = 'Call Patient';
    public $confirmText = 'Are you sure you want to call the next patient?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $calledCount = 0;
        $notes = $fields->get('call_notes');

        foreach ($models as $queuePatient) {
            if ($queuePatient->status === 'waiting') {
                $queuePatient->update([
                    'status' => 'called',
                    'called_at' => now(),
                    'notes' => $queuePatient->notes . ($notes ? "\nCalled: " . $notes : ''),
                ]);
                $calledCount++;
            }
        }

        if ($calledCount > 0) {
            return Action::message("Successfully called {$calledCount} patients");
        } else {
            return Action::danger("No waiting patients found to call");
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
            Textarea::make('Call Notes')
                ->nullable()
                ->help('Optional notes about the call'),
        ];
    }

}
