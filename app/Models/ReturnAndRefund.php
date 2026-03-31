<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ReturnAndRefund extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $table = "return_and_refunds";
    protected $fillable = ['return_reason_id', 'note', 'reject_reason', 'order_id', 'user_id', 'order_serial_no', 'status', 'refund_status', 'refund_issued_at', 'admin_viewed_at'];
    protected $casts = [
        'id'               => 'integer',
        'return_reason_id' => 'integer',
        'note'             => 'string',
        'reject_reason'    => 'string',
        'order_id'         => 'integer',
        'user_id'          => 'integer',
        'order_serial_no'  => 'string',
        'status'           => 'integer',
        'refund_status'    => 'integer',
        'refund_issued_at' => 'datetime',
        'admin_viewed_at'  => 'datetime',
    ];

    public function getImagesAttribute(): array
    {
        $response = [];
        if (!empty($this->getFirstMediaUrl('return'))) {
            $images = $this->getMedia('return');
            foreach ($images as $image) {
                $response[] = $image['original_url'];
            }
        }
        return $response;
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 112, 120)->keepOriginalImageFormat()->sharpen(10);
    }

    public function returnProducts(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(ReturnAndRefundProduct::class, 'return_and_refund_id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function returnReason(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
