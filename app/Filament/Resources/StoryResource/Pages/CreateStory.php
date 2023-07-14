<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Actions\Story\LikeStoryAction;
use App\Actions\Story\RateStoryAction;
use App\Actions\Story\ReadStoryAction;
use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateStory extends CreateRecord
{
    protected static string $resource = StoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        return parent::mutateFormDataBeforeCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function() use($data) {
            $user_like = $data['user_like_exists'];
            unset($data['user_like_exists']);
            $user_rating = $data['user_rating_min_rating'];
            unset($data['user_rating_min_rating']);
            $user_read = $data['user_read_exists'];
            unset($data['user_read_exists']);

            $record = Story::create($data);

            if ($user_like) {
                (new LikeStoryAction)->execute($record, auth()->user());
            }

            if ($user_read) {
                (new ReadStoryAction())->execute($record, auth()->user());
            }

            if (!empty($user_rating)) {
                (new RateStoryAction)->execute($record, auth()->user(), $user_rating);
            }

            return $record;
        });
    }
}
