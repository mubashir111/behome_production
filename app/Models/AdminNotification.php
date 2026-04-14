<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'type', 'title', 'body', 'link', 'icon', 'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Create a notification and prune old ones (keep last 100).
     */
    public static function record(string $type, string $title, string $body = '', string $link = '', string $icon = 'bell'): self
    {
        $n = self::create([
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'link'    => $link,
            'icon'    => $icon,
            'is_read' => false,
        ]);

        // Keep only the latest 100
        $oldest = self::orderBy('created_at', 'desc')->skip(100)->first();
        if ($oldest) {
            self::where('created_at', '<', $oldest->created_at)->delete();
        }

        return $n;
    }
}
