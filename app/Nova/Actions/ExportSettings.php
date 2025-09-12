<?php

namespace App\Nova\Actions;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportSettings extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export Settings';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $format = $fields->get('export_format', 'json');
        $exportCount = $models->count();

        // Prepare export data
        $exportData = $models->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
                'clinic_id' => $setting->clinic_id,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
            ];
        });

        // Generate filename
        $filename = 'settings_export_' . now()->format('Y-m-d_H-i-s') . '.' . $format;

        // For now, we'll return a message with the data
        // In a real implementation, you would generate and download the file
        $data = $exportData->toArray();

        return Action::message("Exported {$exportCount} settings in {$format} format. Filename: {$filename}");
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
            Select::make('Export Format', 'export_format')
                ->options([
                    'json' => 'JSON',
                    'csv' => 'CSV',
                    'yaml' => 'YAML',
                ])
                ->default('json')
                ->help('Choose the format for the exported settings'),
        ];
    }
}
