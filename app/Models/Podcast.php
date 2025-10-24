<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'podcaster', 'cover_url'];

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
