<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewTag extends ViewRecord
{
    protected static string $resource = TagResource::class;

    public function getHeading(): string|Htmlable
    {
        /** @var Tag $record */
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
}
