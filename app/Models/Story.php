<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'user_id', 'body', 'original_url', 'story_series_id', 'story_series_order'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(StorySeries::class, 'story_series_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->using(StoryTag::class)
            ->withTimestamps();
    }

    public function ratingTags(): BelongsToMany
    {
        return $this->belongsToMany(RatingTag::class)
            ->using(RatingTagStory::class)
            ->withTrashed()
            ->withPivot('rating');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryStory::class)
            ->withTimestamps();
    }

    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_like')
            ->using(StoryLikes::class)
            ->withTimestamps();
    }

    public function userLike(): HasOne
    {
        return $this->hasOne(StoryLikes::class)
            ->where('user_id', auth()->id());
    }

    public function ratedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_rating')
            ->withPivot('rating')
            ->using(StoryRating::class)
            ->withTimestamps();
    }

    public function userRating(): HasOne
    {
        return $this->hasOne(StoryRating::class)
            ->where('user_id', auth()->id());
    }

    public function readUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_read')
            ->using(StoryRead::class)
            ->withTimestamps();
    }

    public function userRead(): HasOne
    {
        return $this->hasOne(StoryRead::class)
            ->where('user_id', auth()->id());
    }
}
