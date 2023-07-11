<?php

namespace App\Filament\Resources\StorySeriesResource\Pages;

use App\Filament\Resources\StorySeriesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStorySeries extends ListRecords
{
    protected static string $resource = StorySeriesResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Story Series')
                ->color('success'),
        ];
    }
}
