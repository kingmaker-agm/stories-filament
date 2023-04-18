<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RatingTagStory extends Pivot
{
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = ['story_id', 'rating_tag_id', 'rating'];

    public function story()
    {
        return $this->belongsTo(Story::class)
            ->withTrashed();
    }

    public function ratingTag()
    {
        return $this->belongsTo(RatingTag::class)
            ->withTrashed();
    }
}
