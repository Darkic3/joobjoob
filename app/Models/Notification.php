<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'type',
        'title', 'body', 'icon', 'link', 'is_read', 'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    // ==================== Relationships ====================

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== Scopes ====================

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId);
    }

    public function scopeForAdmins(Builder $query): Builder
    {
        return $query->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', User::where('is_admin', true)->pluck('id'));
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ==================== Methods ====================

    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public static function markAllAsRead(int $userId): int
    {
        return static::forUser($userId)
            ->unread()
            ->update(['is_read' => true]);
    }

    public static function getUnreadCount(int $userId): int
    {
        return static::forUser($userId)->unread()->count();
    }

    public static function getAdminUnreadCount(): int
    {
        return static::forAdmins()->unread()->count();
    }
}
