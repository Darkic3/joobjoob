<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تک‌زبانه‌سازی پنل ادمین (فارسی):
 *
 * ۱. فقط زبان fa فعال و پیش‌فرض می‌ماند؛ بقیه غیرفعال می‌شوند (حذف نمی‌شوند
 *    تا ریدایرکت‌های 301 قدیمی /en/... همچنان به canonical برگردند).
 * ۲. پرمیشن‌های مرده‌ی admin.languages.* از جدول permissions و پیوت نقش‌ها
 *    پاک می‌شوند (مدیریت زبان‌ها از پنل حذف شده است).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('languages')) {
            // تضمین وجود fa
            $faExists = DB::table('languages')->where('code', 'fa')->exists();

            if ($faExists) {
                DB::table('languages')->where('code', 'fa')->update([
                    'is_active' => true,
                    'is_default' => true,
                ]);

                DB::table('languages')->where('code', '!=', 'fa')->update([
                    'is_active' => false,
                    'is_default' => false,
                ]);
            }
        }

        if (Schema::hasTable('permissions')) {
            $ids = DB::table('permissions')
                ->where('name', 'like', 'admin.languages.%')
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
                }

                DB::table('permissions')->whereIn('id', $ids)->delete();
            }
        }
    }

    public function down(): void
    {
        // برگشت‌پذیر نیست به‌صورت خودکار: وضعیت قبلی زبان‌ها و پرمیشن‌ها
        // نامشخص است. در صورت نیاز، LanguageSeeder را دوباره اجرا کنید.
    }
};
