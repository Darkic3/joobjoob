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
        'review.approved' => ['icon' => '✅', 'color' => 'success'],
        'review.replied' => ['icon' => '↩️', 'color' => 'info'],

        // ── Admin notifications ──
        'order.new' => ['icon' => '📦', 'color' => 'accent'],
        'order.paid_admin' => ['icon' => '✅', 'color' => 'success'],
        'order.card_to_card' => ['icon' => '💳', 'color' => 'warning'],
        'review.new' => ['icon' => '💬', 'color' => 'info'],
        'contact.new' => ['icon' => '✉️', 'color' => 'accent'],
    ];

    // تک‌منبع حقیقت تفکیک مخاطب — آینه ثابت‌های مدل (برای سازگاری کالرهای قدیمی)
    public const USER_TYPES = Notification::USER_TYPES;
    public const ADMIN_TYPES = Notification::ADMIN_TYPES;

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
        $body  = trans("notifications.{$type}.body", $data ?? [], $locale);
        if ($body === "notifications.{$type}.body") {
            $body = null;
        }
        $meta  = self::TYPES[$type] ?? ['icon' => '🔔', 'color' => 'accent'];

        return Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'audience' => 'user',
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
            $body  = trans("notifications.{$type}.body", $data ?? [], $locale);
            if ($body === "notifications.{$type}.body") {
                $body = null;
            }

            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'type' => $type,
                'audience' => 'admin',
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
     * تعداد اعلان‌های خوانده‌نشده کاربر (فقط audience=user — بج سایت)
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::getUnreadCount($user->id, 'user');
    }

    /**
     * تعداد اعلان‌های خوانده‌نشده ادمین (شخصی + فقط audience=admin — بج پنل)
     * اگر ادمین پاس داده نشود، برای سازگاری شمارش گلوبال فیلترشده برمی‌گردد.
     */
    public function getAdminUnreadCount(?User $admin = null): int
    {
        if ($admin === null) {
            return Notification::getAdminUnreadCount();
        }

        return Notification::getAdminUnreadCount($admin->id);
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
     * خوانده‌شده همه اعلان‌های کاربر (با تفکیک audience تا پنل/سایت همدیگر را نخوانند)
     */
    public function markAllAsRead(User $user, ?string $audience = null): int
    {
        return Notification::markAllAsRead($user->id, $audience);
    }

    public function markAllUserAsRead(User $user): int
    {
        return Notification::markAllAsRead($user->id, 'user');
    }

    public function markAllAdminAsRead(User $admin): int
    {
        return Notification::markAllAsRead($admin->id, 'admin');
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
     * لیست اعلانات ادمین — شخصی (فقط کپی همین ادمین) + فقط audience=admin
     * امضای قدیمی getAdminNotifications($perPage, $filter) همچنان پشتیبانی می‌شود.
     */
    public function getAdminNotifications(User|int $adminOrPerPage = 20, int|string|null $perPageOrFilter = 20, ?string $filter = null): LengthAwarePaginator
    {
        if ($adminOrPerPage instanceof User) {
            $admin = $adminOrPerPage;
            $perPage = is_int($perPageOrFilter) ? $perPageOrFilter : 20;
            $activeFilter = is_string($perPageOrFilter) ? $perPageOrFilter : $filter;
            $query = Notification::forUser($admin->id)->adminNotifications()->latest();
            $this->applyAdminFilter($query, $activeFilter);

            return $query->paginate($perPage);
        }

        // سازگاری عقب‌رو: getAdminNotifications(20, $filter) → شمارش گلوبال فیلترشده
        $perPage = (int) $adminOrPerPage;
        $activeFilter = is_string($perPageOrFilter) ? $perPageOrFilter : $filter;
        $query = Notification::forAdmins()->adminNotifications()->latest();
        $this->applyAdminFilter($query, $activeFilter);

        return $query->paginate($perPage);
    }

    private function applyAdminFilter($query, ?string $activeFilter): void
    {
        if ($activeFilter === 'unread') {
            $query->unread();
        } elseif ($activeFilter && $activeFilter !== 'all') {
            $query->ofType($activeFilter);
        }
    }

    /**
     * دریافت رنگ یک نوع اعلان
     */
    public static function getTypeColor(string $type): string
    {
        return (self::TYPES[$type] ?? [])['color'] ?? 'accent';
    }
}
