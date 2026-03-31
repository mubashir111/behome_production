<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $table    = 'blog_posts';
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'cover_image',
        'category', 'author', 'is_published', 'published_at',
        'meta_title', 'meta_description', 'views',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views'        => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class)->where('is_approved', true)->latest();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }
}
