<?php

namespace App\Actions\Story;

use App\Models\Story;
use App\Models\User;

class RateStoryAction
{
    public function execute(Story $story, User|int $user, int $rating): void
    {
        $user = $user instanceof User ? $user : User::findOrFail($user);

        $story->ratedUsers()->syncWithoutDetaching([$user->id => ['rating' => $rating]]);
    }
}
