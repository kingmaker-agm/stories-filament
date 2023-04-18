<?php

namespace App\Filament\Resources\RatingTagResource\Pages;

use App\Filament\Resources\RatingTagResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRatingTag extends ViewRecord
{
    protected static string $resource = RatingTagResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
