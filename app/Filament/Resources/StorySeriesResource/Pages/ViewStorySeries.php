<?php

namespace App\Filament\Resources\StorySeriesResource\Pages;

use App\Filament\Resources\StorySeriesResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewStorySeries extends ViewRecord
{
    protected static string $resource = StorySeriesResource::class;

    public function getHeading(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
