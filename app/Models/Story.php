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

    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->using(StoryTag::class)
            ->withTimestamps();
    }

    public function ratingTags()
    {
        return $this->belongsToMany(RatingTag::class)
            ->using(RatingTagStory::class)
            ->withTrashed()
            ->withPivot('rating');
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'story_like')
            ->using(StoryLikes::class)
            ->withTimestamps();
    }

    public function userLike()
    {
        return $this->hasOne(StoryLikes::class)
            ->where('user_id', auth()->id());
    }

    public function ratedUsers()
    {
        return $this->belongsToMany(User::class, 'story_rating')
            ->withPivot('rating')
            ->using(StoryRating::class)
            ->withTimestamps();
    }

    public function userRating()
    {
        return $this->hasOne(StoryRating::class)
            ->where('user_id', auth()->id());
    }

    public function readUsers()
    {
        return $this->belongsToMany(User::class, 'story_read')
            ->using(StoryRead::class)
            ->withTimestamps();
    }

    public function userRead()
    {
        return $this->hasOne(StoryRead::class)
            ->where('user_id', auth()->id());
    }
}
