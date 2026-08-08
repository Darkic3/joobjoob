<?php

namespace App\Models\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

/**
 * دسترسی‌های مدیریتی کاربر (RBAC مبتنی بر نام روت).
 * با توجه به بلوپرینت documents/AUTH_ADMIN_REPORT.md
 */
trait HasAdminPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * نام همه دسترسی‌های همه نقش‌های کاربر (کش‌شده به مدت ۱ ساعت).
     * کلید کش نسخه‌دار است: وقتی دسترسی‌های یک نقش تغییر کند (bumpPermissionsVersion)،
     * کش همه کاربران یکجا باطل می‌شود — نه فقط کاربر جاری.
     */
    public function permissionNames(): array
    {
        $version = Cache::rememberForever('admin.permissions.version', fn () => 1);

        return Cache::remember(
            "admin.permissions.v{$version}.{$this->id}",
            now()->addHour(),
            // مستقیم از رابطه query بزن (نه از relation های از قبل لودشده روی مدل)
            // تا بعد از bump نسخه، نتیجه همیشه تازه باشد. فقط در cache miss اجرا می‌شود.
            fn () => $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions)
                ->pluck('name')
                ->unique()
                ->values()
                ->all()
        );
    }

    /**
     * آیا کاربر به این نام روت دسترسی دارد؟
     * سوپرادمین (is_admin) همیشه true است — هرگز قفل نمی‌شود.
     */
    public function canAccess(string $routeName): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return in_array($routeName, $this->permissionNames(), true);
    }

    /**
     * کش دسترسی‌های فقط همین کاربر را پاک کن (بعد از sync نقش‌های خودش).
     */
    public function forgetPermissionCache(): void
    {
        Cache::forget("admin.permissions.v{$this->version()}.{$this->id}");
    }

    /**
     * نسخه فعلی کش دسترسی‌ها.
     */
    protected function version(): int
    {
        return (int) Cache::get('admin.permissions.version', 1);
    }

    /**
     * بعد از هر تغییر در دسترسی‌های یک نقش یا کاتالوگ دسترسی‌ها صدا بزن:
     * کش همه کاربران یکجا باطل می‌شود.
     *
     * مقاوم در برابر کلید ناموجود: درایور database روی کلید ناموجود increment نمی‌کند،
     * پس اگر کلید نبود، از مقدار ۲ شروع می‌کنیم (مقدار ۱ متعلق به وضعیت قبل است).
     */
    public static function bumpPermissionsVersion(): void
    {
        if (Cache::has('admin.permissions.version')) {
            Cache::increment('admin.permissions.version');
        } else {
            Cache::forever('admin.permissions.version', 2);
        }
    }
}
