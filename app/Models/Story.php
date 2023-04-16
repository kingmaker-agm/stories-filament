<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'user_id', 'body', 'original_url', 'story_series_id', 'story_series_order'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function series()
    {
        return $this->belongsTo(StorySeries::class, 'story_series_id');
    }

    public function seriesStories()
    {
        return $this->through('series')->has('stories');
    }
}
