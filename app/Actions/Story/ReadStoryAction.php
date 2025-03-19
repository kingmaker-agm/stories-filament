<?php

namespace App\Actions\Story;

use App\Models\Story;
use App\Models\User;

class ReadStoryAction
{
    public function execute(Story $story, User|int $user): void
    {
        $user = $user instanceof User ? $user : User::findOrFail($user);

        $story->readUsers()->syncWithoutDetaching([$user->id]);
        $story->refresh();
    }
}
