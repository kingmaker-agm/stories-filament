<?php

namespace App\Actions\Story;

use App\Models\Story;
use App\Models\User;

class UnreadStoryAction
{
    public function execute(Story $story, User|int $user): void
    {
        $user = $user instanceof User ? $user : User::findOrFail($user);

        $story->readUsers()->detach([$user->id]);
        $story->refresh();
    }
}
