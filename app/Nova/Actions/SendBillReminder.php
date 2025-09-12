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

class SendBillReminder extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Send Bill Reminder';
    public $confirmButtonText = 'Send Reminder';
    public $confirmText = 'Are you sure you want to send bill reminders?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $reminderCount = 0;
        $reminderType = $fields->get('reminder_type');
        $customMessage = $fields->get('custom_message');

        foreach ($models as $bill) {
            if (in_array($bill->status, ['pending', 'overdue'])) {
                // In a real implementation, you would send the actual reminder
                // For now, we'll just update the bill with reminder information
                $bill->update([
                    'last_reminder_sent_at' => now(),
                    'reminder_count' => ($bill->reminder_count ?? 0) + 1,
                    'notes' => $bill->notes . "\nReminder sent: " . $reminderType . ($customMessage ? " - " . $customMessage : ''),
                ]);
                $reminderCount++;
            }
        }

        if ($reminderCount > 0) {
            return Action::message("Successfully sent {$reminderCount} bill reminders");
        } else {
            return Action::danger("No bills found to send reminders for");
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
            Select::make('Reminder Type')
                ->options([
                    'first_notice' => 'First Notice',
                    'second_notice' => 'Second Notice',
                    'final_notice' => 'Final Notice',
                    'overdue_notice' => 'Overdue Notice',
                ])
                ->rules('required')
                ->help('Select the type of reminder'),

            Textarea::make('Custom Message')
                ->nullable()
                ->help('Optional custom message to include in the reminder'),
        ];
    }

}
