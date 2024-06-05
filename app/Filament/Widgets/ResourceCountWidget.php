<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\RatingTagResource;
use App\Filament\Resources\StoryResource;
use App\Filament\Resources\StorySeriesResource;
use App\Filament\Resources\TagResource;
use Awcodes\Overlook\Widgets\OverlookWidget;

class ResourceCountWidget extends OverlookWidget
{
    protected static ?int $sort = -1;

    public function getIncludes(): array
    {
        return [
            StoryResource::class,
            StorySeriesResource::class,
            RatingTagResource::class,
            TagResource::class,
            CategoryResource::class,
        ];
    }
}
