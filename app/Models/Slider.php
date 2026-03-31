<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "sliders";
    protected $fillable = ['title', 'description', 'image', 'link', 'button_text', 'badge_text', 'status'];
    protected $casts = [
        'id'          => 'integer',
        'title'       => 'string',
        'description' => 'string',
        'status'      => 'integer',
        'link'        => 'string',
        'button_text' => 'string',
        'badge_text'  => 'string',
    ];

    public function getImageAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('slider'))) {
            $slider = $this->getMedia('slider')->last();
            return $slider->getUrl('cover');
        }
        return asset('images/default/slider.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('cover')
            ->fit('crop', 1920, 900)
            ->format('webp')
            ->quality(90)
            ->sharpen(10)
            ->nonQueued();
    }
}
