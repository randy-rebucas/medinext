<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class Setting extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Setting>
     */
    public static $model = \App\Models\Setting::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'key';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'key', 'group', 'description',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Settings';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Setting';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'settings';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Heading::make('Setting Information'),

            Text::make('Key')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Unique identifier for the setting'),

            Text::make('Category', function () {
                return $this->category;
            })->sortable(),

            Text::make('Group Display Name', function () {
                return $this->group_display_name;
            })->onlyOnDetail(),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex()
                ->help('Description of what this setting controls'),

            Heading::make('Setting Configuration'),

            Select::make('Type')
                ->options([
                    'string' => 'String',
                    'integer' => 'Integer',
                    'boolean' => 'Boolean',
                    'array' => 'Array',
                    'object' => 'Object',
                    'json' => 'JSON',
                ])
                ->sortable()
                ->rules('required', 'max:50')
                ->help('Data type for the setting value'),

            Text::make('Type Display Name', function () {
                return $this->type_display_name;
            })->onlyOnDetail(),

            Text::make('Group')
                ->sortable()
                ->rules('required', 'max:100')
                ->help('Category group for the setting'),

            Heading::make('Setting Value'),

            Code::make('Value')
                ->json()
                ->rules('required')
                ->help('Setting value (JSON format)'),

            Text::make('Formatted Value', function () {
                return $this->formatted_value;
            })->onlyOnDetail(),

            Heading::make('Access Control'),

            Boolean::make('Is Public', 'is_public')
                ->sortable()
                ->help('Whether this setting is publicly accessible'),

            BelongsTo::make('Clinic')
                ->nullable()
                ->searchable()
                ->sortable()
                ->help('Clinic-specific setting (leave empty for global)'),

            Heading::make('Validation & Requirements'),

            Badge::make('Required', function () {
                return $this->is_required ? 'Yes' : 'No';
            })->map([
                'Yes' => 'danger',
                'No' => 'success',
            ])->sortable(),

            Badge::make('Editable', function () {
                return $this->is_editable ? 'Yes' : 'No';
            })->map([
                'Yes' => 'success',
                'No' => 'warning',
            ])->sortable(),

            Badge::make('Valid', function () {
                return $this->is_valid ? 'Yes' : 'No';
            })->map([
                'Yes' => 'success',
                'No' => 'danger',
            ])->onlyOnDetail(),

            Heading::make('Help & Context'),

            Text::make('Help Text', function () {
                return $this->help_text;
            })->onlyOnDetail(),

            Text::make('Validation Rules', function () {
                $rules = $this->validation_rules;
                return empty($rules) ? 'None' : implode(', ', $rules);
            })->onlyOnDetail(),

            Text::make('Dependencies', function () {
                $deps = $this->dependencies;
                return empty($deps) ? 'None' : implode(', ', $deps);
            })->onlyOnDetail(),

            Heading::make('Usage Statistics'),

            Text::make('Cache Key', function () {
                return $this->usage_statistics['cache_key'] ?? 'N/A';
            })->onlyOnDetail(),

            Text::make('Has Validation', function () {
                return $this->usage_statistics['has_validation'] ? 'Yes' : 'No';
            })->onlyOnDetail(),

            Heading::make('Timestamps'),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new StatusFilter,
            new DateRangeFilter,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [
            new ActiveRecords,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new ExportData,
            new BulkUpdate,
        ];
    }
}
