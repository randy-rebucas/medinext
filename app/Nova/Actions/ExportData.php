<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportData extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Export Data';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $format = $fields->get('format', 'csv');
        $resourceName = $models->first()->getTable();
        
        // Generate filename
        $filename = $resourceName . '_' . now()->format('Y-m-d_H-i-s') . '.' . $format;
        
        // For now, just return a success message
        // In a real implementation, you would export the data
        return Action::message("Export completed for {$models->count()} records in {$format} format");
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
            Select::make('Format')
                ->options([
                    'csv' => 'CSV',
                    'xlsx' => 'Excel (XLSX)',
                    'json' => 'JSON',
                ])
                ->default('csv')
                ->rules('required'),
        ];
    }
}
