<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'title_eng', 'title_bur', 'content_eng', 'content_bur', 'number'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subsessions()
    {
        return $this->hasMany(Subsession::class);
    }
}
