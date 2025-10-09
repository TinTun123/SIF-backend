<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'number',
        'title_eng',
        'title_bur',
        'content_eng',
        'content_bur',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
