<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Actions\Story\LikeStoryAction;
use App\Actions\Story\RateStoryAction;
use App\Actions\Story\RemoveStoryRatingAction;
use App\Actions\Story\UnlikeStoryAction;
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
                $likeStoryAction = new LikeStoryAction;
                $likeStoryAction->execute($record, auth()->user());
            } else {
                $unlikeStoryAction = new UnlikeStoryAction;
                $unlikeStoryAction->execute($record, auth()->user());
            }

            if (empty($user_rating)) {
                $removeStoryRatingAction = new RemoveStoryRatingAction;
                $removeStoryRatingAction->execute($record, auth()->user());
            } else {
                $rateStoryAction = new RateStoryAction;
                $rateStoryAction->execute($record, auth()->user(), $user_rating);
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
