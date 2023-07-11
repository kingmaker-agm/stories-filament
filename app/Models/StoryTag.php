<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StoryTag extends Pivot
{
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
