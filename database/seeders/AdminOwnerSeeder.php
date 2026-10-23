<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * سیدر ادمین اصلی (Admin Owner) + نقش main + کاتالوگ دسترسی‌ها
 *
 * این سیدر بعد از اجرای میگریشن‌های RBAC (roles / permissions / permission_role / role_user
 * و ستون users.is_admin) اجرا می‌شود و موارد زیر را به‌صورت ایدمپوتنت انجام می‌دهد:
 *
 *  1) تمام دسترسی‌های درخت کانفیگ (config/permissions.php) را در جدول permissions upsert می‌کند
 *  2) نقش سیستمی «main» را با تمام دسترسی‌ها می‌سازد (is_system = true → قابل حذف نیست)
 *  3) ادمین اصلی را با is_admin = true و نقش main می‌سازد
 *
 * اعتبارنامه‌ها از config('admin.owner.*') خوانده می‌شوند (فایل config/admin.php که مقادیر را
 * از env می‌گیرد) — هرگز مستقیم env() خوانده نمی‌شود، چون بعد از `php artisan config:cache`
 * مقدار env() خارج از فایل‌های کانفیگ null است. برای پورت به سایت‌های دیگر فقط همین کانفیگ و
 * سیدر کافی است.
 *
 * استفاده:
 *   php artisan migrate
 *   php artisan db:seed --class=AdminOwnerSeeder
 */
class AdminOwnerSeeder extends Seeder
{
    public function run(): void
    {
        // ───────────────────────── ۱) کاتالوگ دسترسی‌ها از کانفیگ ─────────────────────────
        $tree = config('permissions.permissions', []);

        if (empty($tree)) {
            $this->command->warn('⚠️ درخت دسترسی‌ها (config/permissions.php) خالی است — هیچ دسترسی‌ای ساخته نشد.');

            return;
        }

        foreach ($tree as $group => $item) {
            foreach ($item['access'] as $access => $labelKey) {
                Permission::updateOrCreate(
                    ['name' => "admin.{$group}.{$access}"],
                    ['group' => $group, 'label_key' => $labelKey],
                );
            }
        }

        $this->command->info('✅ کاتالوگ دسترسی‌ها همگام‌سازی شد ('.Permission::count().' دسترسی).');

        // ───────────────────────── ۲) نقش سیستمی main (دسترسی کامل) ─────────────────────────
        $mainRole = Role::updateOrCreate(
            ['name' => 'main'],
            ['is_system' => true],
        );

        $mainRole->permissions()->sync(Permission::pluck('id')->all());

        // تغییر در دسترسی‌های یک نقش → باطل‌کردن کش همه کاربران
        User::bumpPermissionsVersion();

        $this->command->info('✅ نقش سیستمی «main» آماده است ('.$mainRole->permissions()->count().' دسترسی).');

        // ───────────────────────── ۳) ادمین اصلی (فقط از env) ─────────────────────────
        // مقادیر از config('admin.owner.*') می‌آیند که خودش از env خوانده می‌شود
        // (config/admin.php). رشته خالی = تنظیم‌نشده = پیش‌فرض + هشدار.
        $owner = config('admin.owner', []);

        $name = trim((string) ($owner['name'] ?? ''));
        $email = strtolower(trim((string) ($owner['email'] ?? '')));
        $mobile = trim((string) ($owner['mobile'] ?? ''));
        $envPassword = (string) ($owner['password'] ?? '');

        $source = [
            'name' => $name !== '' ? 'env' : 'fallback',
            'email' => $email !== '' ? 'env' : 'fallback',
            'mobile' => $mobile !== '' ? 'env' : 'fallback',
            'password' => $envPassword !== '' ? 'env' : 'generated',
        ];

        if ($name === '') {
            $name = 'مدیر اصلی';
        }
        if ($email === '') {
            $email = 'admin@joobjoob.test';
        }
        if ($mobile === '') {
            $mobile = '09120000000';
        }

        if (app()->configurationIsCached()) {
            $this->command->warn('⚠️ کانفیگ کش شده است (config:cache). اگر .env را تازه ویرایش کرده‌اید، اول `php artisan config:clear` بزنید؛ وگرنه مقادیر قدیمی سید می‌شوند.');
        }

        // اول با ایمیل، بعد با موبایل — orWhere بدون گروه‌بندی ممکن است رکورد اشتباه بدهد
        $user = User::where('email', $email)->first()
            ?? User::where('mobile', $mobile)->first()
            ?? new User;

        $user->name = $name;
        $user->email = $email;
        $user->mobile = $mobile;
        $user->is_admin = true;
        $user->is_active = true;

        // ⚠️ رمز را خام بدهید — مدل User کست `hashed` دارد و خودش هش می‌کند.
        // صدا زدن Hash::make در اینجا ممنوع (در لاراول‌های قدیمی دابل‌هش و لاگین غیرممکن می‌شد).
        $generatedPassword = null;
        if ($envPassword !== '') {
            $user->password = $envPassword;
        } elseif (! $user->exists) {
            $generatedPassword = Str::password(16);
            $user->password = $generatedPassword;
        }

        $user->save();

        $user->roles()->sync([$mainRole->id]);

        if (method_exists($user, 'forgetPermissionCache')) {
            $user->forgetPermissionCache();
        }

        $this->command->info("✅ ادمین اصلی آماده ورود است: {$email} / موبایل: {$mobile}");
        foreach ($source as $field => $from) {
            $this->command->line("   • {$field}: ".($from === 'env' ? 'از env خوانده شد' : ($from === 'generated' ? 'رمز تصادفی ساخته شد' : '⚠️ env خالی بود — پیش‌فرض استفاده شد')));
        }

        // رمز تولیدشده فقط همین یک بار نمایش داده می‌شود
        if ($generatedPassword !== null) {
            $this->command->warn("رمز عبور تولیدشده (فقط همین یک بار نمایش داده می‌شود): {$generatedPassword}");
        }
    }
}
