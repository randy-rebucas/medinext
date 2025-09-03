<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Actions\ExportData;
use App\Nova\Actions\BulkUpdate;
use App\Nova\Filters\StatusFilter;
use App\Nova\Filters\DateRangeFilter;
use App\Nova\Lenses\ActiveRecords;

class FileAsset extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\FileAsset>
     */
    public static $model = \App\Models\FileAsset::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'file_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'file_name', 'original_name', 'category', 'description', 'clinic_id'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'File Assets';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'File Asset';
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

            BelongsTo::make('Clinic')
                ->sortable()
                ->searchable()
                ->rules('required', 'exists:clinics,id'),

            Text::make('File Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Internal file name in the system'),

            Text::make('Original Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('Original filename as uploaded by user'),

            URL::make('URL')
                ->sortable()
                ->rules('required', 'url', 'max:2048')
                ->help('File storage URL'),

            Text::make('MIME Type', 'mime')
                ->sortable()
                ->rules('required', 'max:100')
                ->help('File MIME type (e.g., image/jpeg, application/pdf)'),

            Number::make('Size (bytes)')
                ->sortable()
                ->rules('required', 'integer', 'min:0')
                ->help('File size in bytes'),

            Text::make('Human Size')
                ->computed(function () {
                    return $this->human_size;
                })
                ->sortable()
                ->hideFromForms()
                ->help('File size in human readable format'),

            Text::make('Checksum')
                ->sortable()
                ->rules('required', 'max:255')
                ->help('File integrity checksum'),

            Select::make('Category')
                ->options([
                    'medical_record' => 'Medical Record',
                    'prescription' => 'Prescription',
                    'lab_result' => 'Lab Result',
                    'imaging' => 'Imaging',
                    'document' => 'Document',
                    'photo' => 'Photo',
                    'video' => 'Video',
                    'audio' => 'Audio',
                    'other' => 'Other'
                ])
                ->sortable()
                ->rules('required')
                ->help('File category classification'),

            Textarea::make('Description')
                ->nullable()
                ->hideFromIndex()
                ->help('Description of the file content'),

            MorphTo::make('Owner')
                ->types([
                    \App\Models\Patient::class,
                    \App\Models\Encounter::class,
                    \App\Models\Prescription::class,
                    \App\Models\LabResult::class,
                    \App\Models\User::class,
                ])
                ->searchable()
                ->rules('required')
                ->help('Entity that owns this file'),

            DateTime::make('Created At')
                ->sortable()
                ->hideFromForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->hideFromForms(),
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
     * @return array<int, \Laravel\Nova\Filters\Filter>
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
     * @return array<int, \Laravel\Nova\Lenses\Lens>
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
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new ExportData,
            new BulkUpdate,
        ];
    }
}
