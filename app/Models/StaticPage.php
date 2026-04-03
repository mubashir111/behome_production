<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $fillable = [
        'slug', 'title', 'content', 'sections',
        'meta_title', 'meta_description', 'is_active', 'is_system',
    ];

    protected $casts = [
        'sections'  => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];
}
