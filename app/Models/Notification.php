<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    public const USER_TYPES = [
        'order.created',
        'order.paid',
        'order.shipped',
        'order.cancelled',
        'discount.welcome',
        'discount.expiring',
        'review.approved',
        'review.replied',
    ];

    public const ADMIN_TYPES = [
        'order.new',
        'order.paid_admin',
        'order.card_to_card',
        'review.new',
        'contact.new',
    ];

    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'type', 'audience',
        'title', 'body', 'icon', 'link', 'is_read', 'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
    ];

    protected static function booted(): void
    {
        // ایمنی برای ساخت مستقیم (تست/سیدر): اگر audience داده نشد از روی type استنتاج شود
        static::creating(function (Notification $notification) {
            if (empty($notification->audience)) {
                $notification->audience = in_array($notification->type, self::ADMIN_TYPES, true)
                    ? 'admin'
                    : 'user';
            }
        });
    }

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

    public function scopeOfAudience(Builder $query, string $audience): Builder
    {
        return $query->where('audience', $audience);
    }

    /**
     * فقط اعلان‌های کاربری — فیلتر کردن اعلان‌های ادمین
     * چون کاربرهای ادمین هم می‌بینند اعلان‌های کاربری در site notifications
     * دفاع دو‌لایه: audience + لیست type (برای رکوردهای قدیمی بدون audience)
     */
    public function scopeUserNotifications(Builder $query): Builder
    {
        return $query->where('audience', 'user')
            ->whereNotIn('type', self::ADMIN_TYPES);
    }

    /**
     * فقط اعلان‌های ادمینی — قرینه userNotifications
     * جلوگیری از نشت اعلان‌های شخصی ادمین (مثل order.created خودش) به پنل
     */
    public function scopeAdminNotifications(Builder $query): Builder
    {
        return $query->where('audience', 'admin')
            ->whereIn('type', self::ADMIN_TYPES);
    }

    // ==================== Methods ====================

    /**
     * برچسب ترجمه‌شده نوع اعلان (مثلاً order.created => «ثبت سفارش»)
     * اگر کلید ترجمه وجود نداشت، خود type خام برمی‌گردد تا چیزی گم نشود.
     */
    public function getTypeLabelAttribute(): string
    {
        $key = 'notifications.'.$this->type.'.label';
        $label = __($key);

        return $label === $key ? (string) $this->type : $label;
    }

    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public static function markAllAsRead(int $userId, ?string $audience = null): int
    {
        $query = static::forUser($userId)->unread();

        if ($audience !== null) {
            $query->ofAudience($audience);
        }

        return $query->update(['is_read' => true]);
    }

    public static function getUnreadCount(int $userId, ?string $audience = null): int
    {
        $query = static::forUser($userId)->unread();

        if ($audience === 'user') {
            $query->userNotifications();
        } elseif ($audience === 'admin') {
            $query->adminNotifications();
        } elseif ($audience !== null) {
            $query->ofAudience($audience);
        }

        return $query->count();
    }

    public static function getAdminUnreadCount(?int $adminId = null): int
    {
        // حالت قدیمی بدون آرگومان (سازگاری با تست‌ها): شمارش گلوبال
        if ($adminId === null) {
            return static::forAdmins()->unread()->adminNotifications()->count();
        }

        return static::forUser($adminId)->unread()->adminNotifications()->count();
    }
}
