<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id', 'title', 'body', 'icon', 'color', 'link', 'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Send to a specific user (or broadcast to all if user_id is null).
     */
    public static function send(
        string $title,
        string $body = '',
        string $type = 'info',
        string $icon = 'bell',
        string $color = '#6366f1',
        string $link = '',
        ?int $userId = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'icon'    => $icon,
            'color'   => $color,
            'link'    => $link,
            'type'    => $type,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user has read this notification.
     */
    public function isReadBy(int $userId): bool
    {
        return \DB::table('user_notification_reads')
            ->where('user_id', $userId)
            ->where('notification_id', $this->id)
            ->exists();
    }
}
