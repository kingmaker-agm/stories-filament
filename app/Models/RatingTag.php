<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingTag extends Model
{
    use SoftDeletes;
    protected $fillable = ['name'];

    public function stories()
    {
        return $this->belongsToMany(Story::class)
            ->using(RatingTagStory::class)
            ->withPivot('rating')
            ->withTimestamps();
    }
}
