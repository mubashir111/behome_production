<?php

namespace App\Models;


use App\Enums\Status;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;


class ProductCategory extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasRecursiveRelationships;

    protected $table = "product_categories";
    protected $fillable = ['name', 'slug', 'description', 'status', 'parent_id'];
    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'slug'        => 'string',
        'description' => 'string',
        'status'      => 'integer',
        'parent_id'   => 'integer',
    ];

    public function scopeActive($query, $col = 'status')
    {
        return $query->where($col, Status::ACTIVE);
    }
    protected $appends = array('cover', 'thumb');

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('product-category'))) {
            $category = $this->getMedia('product-category')->last();
            return $category->getUrl('thumb');
        }
        return asset('images/default/category/thumb.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('product-category'))) {
            $category = $this->getMedia('product-category')->last();
            return $category->getUrl('cover');
        }
        return asset('images/default/category/cover.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit('crop', 400, 400)
            ->keepOriginalImageFormat()
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('cover')
            ->fit('crop', 800, 800)
            ->keepOriginalImageFormat()
            ->sharpen(10)
            ->nonQueued();
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class)->where(['status' => Status::ACTIVE]);
    }

    public function parent_category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }
}
