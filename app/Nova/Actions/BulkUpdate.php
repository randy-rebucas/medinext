<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;

class BulkUpdate extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Bulk Update';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $field = $fields->get('field');
        $value = $fields->get('value');
        $booleanValue = $fields->get('boolean_value');
        
        $updatedCount = 0;
        
        foreach ($models as $model) {
            if ($field && $value !== null) {
                $model->update([$field => $value]);
                $updatedCount++;
            } elseif ($field && $booleanValue !== null) {
                $model->update([$field => $booleanValue]);
                $updatedCount++;
            }
        }
        
        return Action::message("Successfully updated {$updatedCount} records");
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
            Select::make('Field')
                ->options([
                    'is_active' => 'Is Active',
                    'name' => 'Name',
                    'email' => 'Email',
                ])
                ->rules('required'),
                
            Text::make('Value')
                ->nullable()
                ->help('Leave empty for boolean fields'),
                
            Boolean::make('Boolean Value')
                ->nullable()
                ->help('Use for boolean fields'),
        ];
    }
}
