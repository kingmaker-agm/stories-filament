<?php

namespace App\Filament\Resources\RatingTagResource\Pages;

use App\Filament\Resources\RatingTagResource;
use App\Models\RatingTag;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewRatingTag extends ViewRecord
{
    protected static string $resource = RatingTagResource::class;

    public function getHeading(): string|Htmlable
    {
        /** @var RatingTag $record */
        $record = $this->getRecord();

        return $record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
