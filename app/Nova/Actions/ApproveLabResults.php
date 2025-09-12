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

class ApproveLabResults extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Approve Lab Results';
    public $confirmButtonText = 'Approve Results';
    public $confirmText = 'Are you sure you want to approve these lab results?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $approvedCount = 0;
        $notes = $fields->get('approval_notes');

        foreach ($models as $labResult) {
            if ($labResult->status === 'completed') {
                $labResult->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'notes' => $labResult->notes . ($notes ? "\nApproved: " . $notes : ''),
                ]);
                $approvedCount++;
            }
        }

        if ($approvedCount > 0) {
            return Action::message("Successfully approved {$approvedCount} lab results");
        } else {
            return Action::danger("No completed lab results found to approve");
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
            Textarea::make('Approval Notes')
                ->nullable()
                ->help('Optional notes about the approval'),
        ];
    }

}
