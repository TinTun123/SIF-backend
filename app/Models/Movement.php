<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = ['title_eng', 'title_bur', 'story_date', 'content_eng', 'content_bur', 'cover_url'];
}
