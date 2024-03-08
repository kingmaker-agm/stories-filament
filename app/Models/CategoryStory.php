<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryStory extends Pivot
{
    protected $fillable = ['category_id', 'story_id'];

    public $timestamps = true;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
