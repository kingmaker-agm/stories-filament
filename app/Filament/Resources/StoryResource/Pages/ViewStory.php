<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewStory extends ViewRecord
{
    protected static string $resource = StoryResource::class;

    public function getHeading(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function resolveRecord($key): Model
    {
        return StoryResource::resolveSingleRecord($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
        ];
    }
}
