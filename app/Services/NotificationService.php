<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    // ==================== Notification Types ====================

    public const TYPES = [
        // ── User notifications ──
        'order.created' => ['icon' => '📦', 'color' => 'accent'],
        'order.paid' => ['icon' => '✅', 'color' => 'success'],
        'order.shipped' => ['icon' => '🚚', 'color' => 'info'],
        'order.cancelled' => ['icon' => '❌', 'color' => 'danger'],
        'discount.welcome' => ['icon' => '🎁', 'color' => 'success'],
        'discount.expiring' => ['icon' => '⏰', 'color' => 'warning'],

        // ── Admin notifications ──
        'order.new' => ['icon' => '📦', 'color' => 'accent'],
        'order.paid_admin' => ['icon' => '✅', 'color' => 'success'],
        'order.card_to_card' => ['icon' => '💳', 'color' => 'warning'],
        'review.new' => ['icon' => '💬', 'color' => 'info'],
        'contact.new' => ['icon' => '✉️', 'color' => 'accent'],
    ];

    // ==================== Create Notifications ====================

    /**
     * ایجاد اعلان برای یک کاربر
     * به‌جای переда کردن رشته‌های ثابت، از کلیدهای ترجمه‌ای استفاده می‌شود
     * تا در ۴ زبان (fa/en/tr/ar) کاملاً محلی‌سازی شود.
     */
    public function notifyUser(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        $locale = app()->getLocale();
        $title = __("notifications.{$type}.title", [], $locale);
        $body  = $body ? trans("notifications.{$type}.body", $data ?? [], $locale) : null;
        $meta  = self::TYPES[$type] ?? ['icon' => '🔔', 'color' => 'accent'];

        return Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'icon' => $meta['icon'],
            'link' => $link,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * ایجاد اعلان برای همه ادمین‌ها
     */
    public function notifyAdmins(
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null,
        ?array $data = null
    ): int {
        $admins = User::where('is_admin', true)->where('is_active', true)->get();
        $meta = self::TYPES[$type] ?? ['icon' => '🔔', 'color' => 'accent'];
        $created = 0;

        foreach ($admins as $admin) {
            $locale = app()->getLocale();
            $title = __("notifications.{$type}.title", [], $locale);
            $body  = $body ? trans("notifications.{$type}.body", $data ?? [], $locale) : null;

            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'icon' => $meta['icon'],
                'link' => $link,
                'data' => $data,
                'is_read' => false,
            ]);
            $created++;
        }

        return $created;
    }

    // ==================== Read/Unread ====================

    /**
     * تعداد اعلان‌های خوانده‌نشده کاربر
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::getUnreadCount($user->id);
    }

    /**
     * تعداد اعلان‌های خوانده‌نشده ادمین‌ها
     */
    public function getAdminUnreadCount(): int
    {
        return Notification::getAdminUnreadCount();
    }

    /**
     * خوانده‌شده یک اعلان
     */
    public function markAsRead(int $id, User $user): bool
    {
        $notification = Notification::forUser($user->id)->find($id);
        if (! $notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * خوانده‌شده همه اعلان‌های کاربر
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::markAllAsRead($user->id);
    }

    // ==================== Get Notifications ====================

    /**
     * لیست اعلانات کاربر
     * فقط اعلان‌های کاربری (نه اعلان‌های ادمین که برای کاربرهای ادمین هم ارسال می‌شوند)
     */
    public function getUserNotifications(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::forUser($user->id)
            ->userNotifications()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * لیست اعلانات ادمین
     */
    public function getAdminNotifications(int $perPage = 20, ?string $filter = null): LengthAwarePaginator
    {
        $query = Notification::forAdmins()->latest();

        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter && $filter !== 'all') {
            $query->ofType($filter);
        }

        return $query->paginate($perPage);
    }

    /**
     * دریافت رنگ یک نوع اعلان
     */
    public static function getTypeColor(string $type): string
    {
        return (self::TYPES[$type] ?? [])['color'] ?? 'accent';
    }
}
