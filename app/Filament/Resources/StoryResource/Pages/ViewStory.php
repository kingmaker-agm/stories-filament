<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewStory extends ViewRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeading(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
        ];
    }
}
