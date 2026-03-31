<?php

namespace App\Models;


use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class Promotion extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "promotions";
    protected $fillable = ['name', 'slug', 'subtitle', 'link', 'type', 'status'];
    protected $casts = [
        'id'       => 'integer',
        'name'     => 'string',
        'slug'     => 'string',
        'subtitle' => 'string',
        'link'     => 'string',
        'type'     => 'integer',
        'status'   => 'integer'
    ];


    public function getCoverAttribute(): string
    {
        $media = $this->getMedia('promotion')->last();
        if ($media) {
            return $media->hasGeneratedConversion('cover') ? $media->getUrl('cover') : $media->getUrl();
        }
        return asset('images/default/promotion/cover.png');
    }

    public function getPreviewAttribute(): string
    {
        $media = $this->getMedia('promotion')->last();
        if ($media) {
            return $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : $media->getUrl();
        }
        return asset('images/default/promotion/preview.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('cover')->crop('crop-center', 360, 224)->format('webp')->quality(90)->sharpen(10);
        $this->addMediaConversion('preview')->width(1126)->height(400)->format('webp')->quality(90)->sharpen(10);
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products');
    }

    public function promotionProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromotionProduct::class, 'promotion_id', 'id');
    }
}
