<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorySeries extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'original_url', 'description'];

    public function stories()
    {
        return $this->hasMany(Story::class, 'story_series_id');
    }
}
