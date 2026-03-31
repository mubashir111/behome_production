<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    protected $fillable = [
        'category', 'question', 'answer', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static $categories = [
        'general'        => 'General',
        'shopping'       => 'Shopping Information',
        'payment'        => 'Payment Information',
        'orders-returns' => 'Orders & Returns',
        'help-support'   => 'Help & Support',
    ];
}
