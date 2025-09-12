<?php

namespace App\Nova\Actions;

use App\Services\LicenseKeyGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class GenerateLicenseKey extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Generate License Key';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $strategy = $fields->strategy ?? LicenseKeyGenerator::STRATEGY_STANDARD;
        $count = $fields->count ?? 1;
        $options = [];

        // Build options based on strategy
        switch ($strategy) {
            case LicenseKeyGenerator::STRATEGY_STANDARD:
                $options['prefix'] = $fields->prefix ?? 'MEDI';
                $options['segment_length'] = $fields->segment_length ?? 4;
                $options['segments'] = $fields->segments ?? 4;
                break;

            case LicenseKeyGenerator::STRATEGY_COMPACT:
                $options['prefix'] = $fields->prefix ?? 'MEDI';
                $options['length'] = $fields->length ?? 12;
                break;

            case LicenseKeyGenerator::STRATEGY_SEGMENTED:
                $options['format'] = $fields->format ?? LicenseKeyGenerator::DEFAULT_FORMAT;
                $options['segment_length'] = $fields->segment_length ?? 4;
                break;

            case LicenseKeyGenerator::STRATEGY_CUSTOM:
                $options['format'] = $fields->custom_format;
                break;
        }

        try {
            if ($count === 1) {
                // Generate single key for selected license
                $license = $models->first();
                $newKey = LicenseKeyGenerator::generate($strategy, $options);

                $license->update(['license_key' => $newKey]);

                $license->addAuditLog('License key regenerated via Nova action', [
                    'strategy' => $strategy,
                    'options' => $options,
                    'generated_by' => 'nova_admin'
                ]);

                return Action::message("License key generated successfully: {$newKey}");
            } else {
                // Generate multiple keys
                $keys = LicenseKeyGenerator::generateMultiple($count, $strategy, $options);

                $keyList = implode("\n", $keys);

                return Action::message("Generated {$count} license keys:\n{$keyList}");
            }
        } catch (\Exception $e) {
            return Action::danger("Failed to generate license key: " . $e->getMessage());
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
            Select::make('Strategy')
                ->options([
                    LicenseKeyGenerator::STRATEGY_STANDARD => 'Standard (MEDI-XXXX-XXXX-XXXX-XXXX)',
                    LicenseKeyGenerator::STRATEGY_COMPACT => 'Compact (MEDI-XXXXXXXXXXXX)',
                    LicenseKeyGenerator::STRATEGY_SEGMENTED => 'Segmented (Custom segments)',
                    LicenseKeyGenerator::STRATEGY_CUSTOM => 'Custom (User-defined format)',
                ])
                ->default(LicenseKeyGenerator::STRATEGY_STANDARD)
                ->help('Choose the license key generation strategy'),

            Number::make('Count')
                ->default(1)
                ->min(1)
                ->max(100)
                ->help('Number of license keys to generate (1 for selected license, more for bulk generation)'),

            Text::make('Prefix')
                ->default('MEDI')
                ->help('License key prefix (e.g., MEDI, STD, PRM)')
                ->dependsOn('strategy', LicenseKeyGenerator::STRATEGY_STANDARD),

            Number::make('Segment Length')
                ->default(4)
                ->min(2)
                ->max(8)
                ->help('Length of each segment in the license key')
                ->dependsOn('strategy', [LicenseKeyGenerator::STRATEGY_STANDARD, LicenseKeyGenerator::STRATEGY_SEGMENTED]),

            Number::make('Segments')
                ->default(4)
                ->min(2)
                ->max(10)
                ->help('Number of segments in the license key')
                ->dependsOn('strategy', LicenseKeyGenerator::STRATEGY_STANDARD),

            Number::make('Length')
                ->default(12)
                ->min(8)
                ->max(20)
                ->help('Total length of the compact license key')
                ->dependsOn('strategy', LicenseKeyGenerator::STRATEGY_COMPACT),

            Text::make('Format')
                ->default(LicenseKeyGenerator::DEFAULT_FORMAT)
                ->help('Custom format for segmented keys. Use {segment1}, {segment2}, etc. as placeholders')
                ->dependsOn('strategy', LicenseKeyGenerator::STRATEGY_SEGMENTED),

            Textarea::make('Custom Format')
                ->help('Custom format for license keys. Use {random:N} for random characters, {timestamp:format} for timestamps, {year}, {month}, {day} for dates')
                ->dependsOn('strategy', LicenseKeyGenerator::STRATEGY_CUSTOM)
                ->rows(3),
        ];
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function authorizedToSee(\Illuminate\Http\Request $request)
    {
        return $request->user() && $request->user()->can('manage-licenses');
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function authorizedToRun(\Illuminate\Http\Request $request, $model)
    {
        return $request->user() && $request->user()->can('manage-licenses');
    }
}
