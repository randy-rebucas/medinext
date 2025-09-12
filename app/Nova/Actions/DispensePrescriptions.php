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

class DispensePrescriptions extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Dispense Prescriptions';
    public $confirmButtonText = 'Dispense Selected';
    public $confirmText = 'Are you sure you want to dispense these prescriptions?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $dispensedCount = 0;
        $dispensedQuantity = $fields->get('dispensed_quantity');
        $notes = $fields->get('dispense_notes');

        foreach ($models as $prescription) {
            if ($prescription->status === 'active') {
                $prescription->update([
                    'status' => 'dispensed',
                    'dispensed_at' => now(),
                    'dispensed_quantity' => $dispensedQuantity,
                    'notes' => $prescription->notes . "\nDispensed: " . $dispensedQuantity . " units" . ($notes ? " - " . $notes : ''),
                ]);
                $dispensedCount++;
            }
        }

        if ($dispensedCount > 0) {
            return Action::message("Successfully dispensed {$dispensedCount} prescriptions");
        } else {
            return Action::danger("No active prescriptions found to dispense");
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
            Number::make('Dispensed Quantity')
                ->rules('required', 'integer', 'min:1')
                ->help('Enter the quantity dispensed'),

            Textarea::make('Dispense Notes')
                ->nullable()
                ->help('Optional notes about the dispensing'),
        ];
    }

}
