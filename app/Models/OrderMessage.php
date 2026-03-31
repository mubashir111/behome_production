<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMessage extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'sender_type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
