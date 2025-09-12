<?php

namespace App\Nova\Actions;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ValidateSettings extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Validate Settings';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $validCount = 0;
        $invalidCount = 0;
        $invalidSettings = [];

        foreach ($models as $setting) {
            if ($setting->isValid()) {
                $validCount++;
            } else {
                $invalidCount++;
                $invalidSettings[] = $setting->key;
            }
        }

        if ($invalidCount > 0) {
            return Action::danger("Found {$invalidCount} invalid settings: " . implode(', ', $invalidSettings));
        } else {
            return Action::message("All {$validCount} settings are valid");
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
        return [];
    }
}
