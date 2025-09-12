<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class RefillPrescriptions extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Refill Prescriptions';
    public $confirmButtonText = 'Refill Selected';
    public $confirmText = 'Are you sure you want to refill these prescriptions?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $refilledCount = 0;
        $refillQuantity = $fields->get('refill_quantity');
        $notes = $fields->get('refill_notes');

        foreach ($models as $prescription) {
            if ($prescription->status === 'active' && $prescription->refills_remaining > 0) {
                $prescription->update([
                    'refills_remaining' => $prescription->refills_remaining - 1,
                    'notes' => $prescription->notes . "\nRefilled: " . $refillQuantity . " units" . ($notes ? " - " . $notes : ''),
                ]);
                $refilledCount++;
            }
        }

        if ($refilledCount > 0) {
            return Action::message("Successfully refilled {$refilledCount} prescriptions");
        } else {
            return Action::danger("No prescriptions found with available refills");
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
            Number::make('Refill Quantity')
                ->rules('required', 'integer', 'min:1')
                ->help('Enter the quantity for refill'),

            Textarea::make('Refill Notes')
                ->nullable()
                ->help('Optional notes about the refill'),
        ];
    }

}
