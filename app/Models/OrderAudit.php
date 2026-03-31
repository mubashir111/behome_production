<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAudit extends Model
{
    protected $table = 'order_audits';

    protected $fillable = [
        'order_id',
        'event',
        'description',
        'meta',
        'actor_type',
        'actor_id',
        'actor_name',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'actor_id' => 'integer',
        'meta'     => 'array',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
