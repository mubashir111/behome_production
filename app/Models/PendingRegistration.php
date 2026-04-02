<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    use HasFactory;

    protected $table = 'pending_registrations';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country_code',
        'password',
        'otp_token'
    ];

    protected $hidden = [
        'password',
    ];
}
