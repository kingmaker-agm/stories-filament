<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['primary', 'secondary'];

    public function stories()
    {
        return $this->belongsToMany(Story::class)
            ->using(StoryTag::class)
            ->withTimestamps();
    }
}
