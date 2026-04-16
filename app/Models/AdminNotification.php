<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Create a notification (idempotent within a 60-second window) and prune old ones (keep last 100).
     * Uses a DB transaction with a pessimistic lock to prevent duplicate rows when two admin
     * tabs poll simultaneously.
     */
    public static function record(string $type, string $title, string $body = '', string $link = '', string $icon = 'bell'): self
    {
        return DB::transaction(function () use ($type, $title, $body, $link, $icon) {
            // lockForUpdate() ensures only one process can pass this check at a time.
            $existing = self::where('type', $type)
                ->where('title', $title)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

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
        });
    }
}
