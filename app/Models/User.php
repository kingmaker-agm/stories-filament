<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function likedStories()
    {
        return $this->belongsToMany(Story::class, 'story_like')
            ->using(StoryLikes::class)
            ->withTimestamps();
    }

    public function ratedStories()
    {
        return $this->belongsToMany(Story::class, 'story_rating')
            ->withPivot('rating')
            ->using(StoryRating::class)
            ->withTimestamps();
    }

    public function readStories()
    {
        return $this->belongsToMany(Story::class, 'story_read')
            ->using(StoryRead::class)
            ->withTimestamps();
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return Str::endsWith($this->email, '@kingmaker.co.in');
    }
}
