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
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class MarkBillAsPaid extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Mark as Paid';
    public $confirmButtonText = 'Mark as Paid';
    public $confirmText = 'Are you sure you want to mark these bills as paid?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $paidCount = 0;
        $paymentAmount = $fields->get('payment_amount');
        $paymentMethod = $fields->get('payment_method');
        $notes = $fields->get('payment_notes');

        foreach ($models as $bill) {
            if (in_array($bill->status, ['pending', 'overdue'])) {
                $bill->update([
                    'status' => 'paid',
                    'paid_amount' => $paymentAmount,
                    'paid_at' => now(),
                    'payment_method' => $paymentMethod,
                    'notes' => $bill->notes . "\nPaid: " . $paymentAmount . " via " . $paymentMethod . ($notes ? " - " . $notes : ''),
                ]);
                $paidCount++;
            }
        }

        if ($paidCount > 0) {
            return Action::message("Successfully marked {$paidCount} bills as paid");
        } else {
            return Action::danger("No bills found to mark as paid");
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
            Number::make('Payment Amount')
                ->rules('required', 'numeric', 'min:0.01')
                ->step(0.01)
                ->help('Enter the payment amount'),

            Select::make('Payment Method')
                ->options([
                    'cash' => 'Cash',
                    'credit_card' => 'Credit Card',
                    'debit_card' => 'Debit Card',
                    'check' => 'Check',
                    'bank_transfer' => 'Bank Transfer',
                    'insurance' => 'Insurance',
                    'other' => 'Other',
                ])
                ->rules('required')
                ->help('Select the payment method'),

            Textarea::make('Payment Notes')
                ->nullable()
                ->help('Optional notes about the payment'),
        ];
    }

}
