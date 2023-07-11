<?php

namespace App\Filament\Resources\StorySeriesResource\Pages;

use App\Filament\Resources\StorySeriesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditStorySeries extends EditRecord
{
    protected static string $resource = StorySeriesResource::class;

    protected function getHeading(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
