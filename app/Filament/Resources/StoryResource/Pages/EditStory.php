<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditStory extends EditRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeading(): string|Htmlable
    {
        return $this->getRecord()->title;
    }

    protected function resolveRecord($key): Model
    {
        return StoryResource::resolveSingleRecord($key);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Story $record */
        return DB::transaction(function () use ($record, $data) {
            $user_like = $data['user_like_exists'];
            unset($data['user_like_exists']);
            $user_rating = $data['user_rating_min_rating'];
            unset($data['user_rating_min_rating']);

            $record->update($data);

            if ($user_like) {
                $record->likedUsers()->syncWithoutDetaching([auth()->id()]);
            } else {
                $record->likedUsers()->detach([auth()->id()]);
            }

            if (empty($user_rating)) {
                $record->ratedUsers()->detach([auth()->id()]);
            } else {
                $record->ratedUsers()->syncWithoutDetaching([auth()->id() => ['rating' => $user_rating]]);
            }

            return StoryResource::resolveSingleRecord($record->getKey());
        });
    }


    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
