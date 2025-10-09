<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['title_eng', 'title_bur', 'cover_url', 'type'];

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
