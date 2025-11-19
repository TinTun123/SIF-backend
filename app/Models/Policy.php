<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_eng',
        'title_bur',
        'date',
        'organizations',
        'content_eng',
        'content_bur',
        'logos',
        'thumbnail'
    ];

    protected $casts = [

        'date' => 'date',
    ];
}
