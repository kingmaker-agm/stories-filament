<?php

namespace App\Filament\Resources\RatingTagResource\Pages;

use App\Filament\Resources\RatingTagResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRatingTags extends ListRecords
{
    protected static string $resource = RatingTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Rating Tag')
                ->color('success'),
        ];
    }
}
