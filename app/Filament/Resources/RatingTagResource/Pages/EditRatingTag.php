<?php

namespace App\Filament\Resources\RatingTagResource\Pages;

use App\Filament\Resources\RatingTagResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRatingTag extends EditRecord
{
    protected static string $resource = RatingTagResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
