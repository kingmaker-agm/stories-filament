<?php

namespace App\Models;

use App\Models\Scopes\AuthUserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class)
            ->withTimestamps()
            ->using(CategoryStory::class);
    }

    protected static function booted()
    {
        parent::booted();
        static::addGlobalScope(new AuthUserScope);
    }


}
