<?php

namespace App\Nova;

use App\Models\Ad as AdModel;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Ad extends Resource
{
    public static $model = AdModel::class;

    public static $title = 'excerpt';

    public static $search = [
        'text',
    ];

    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Text')
                ->onlyOnIndex()
                ->displayUsing(function (string $text) {
                    return Str::limit($text, 50);
                }),

            Markdown::make('Text')
                ->hideFromIndex()
                ->rules('required'),

            Text::make('Display on URL')
                ->hideFromIndex(),

            Date::make('starts_at'),

            Date::make('ends_at'),
        ];
    }
}
