<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SettingService
{
    private const CACHE_PREFIX = 'shopora_setting_';


    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::CACHE_PREFIX . $key, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                // اگر در دیتابیس نبود، مقدار پیش‌فرض را از کانفیگ بخوان
                return $this->getDefaultFromConfig($key) ?? $default;
            }

            return $this->castValue($setting->value, $setting->type);
        });
    }

    /**
     * دریافت تمام تنظیمات یک گروه خاص
     */
    public function getGroup(string $group): array
    {
        $cacheKey = self::CACHE_PREFIX . 'group_' . $group;

        return Cache::rememberForever($cacheKey, function () use ($group) {
            $settings = Setting::where('group', $group)->get()->pluck('value', 'key')->toArray();

            // پر کردن مقادیر خالی با defaults از کانفیگ
            $configFields = config("settings.{$group}.fields", []);
            foreach ($configFields as $key => $field) {
                if (!isset($settings[$key])) {
                    $settings[$key] = $field['default'] ?? null;
                }
            }

            return $settings;
        });
    }

    /**
     * به‌روزرسانی تنظیمات (با مدیریت فایل و تراکنش)
     */
    public function updateGroup(string $group, array $data, array $files = []): void
    {
        DB::transaction(function () use ($group, $data, $files) {
            $configFields = config("settings.{$group}.fields", []);

            foreach ($configFields as $key => $field) {
                $value = $data[$key] ?? null;
                $file = $files[$key] ?? null;

                // اگر فایل آپلود شده باشد
                if ($file instanceof UploadedFile) {
                    $value = $this->handleFileUpload($key, $file, $field['type']);
                }

                // ذخیره در دیتابیس
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
                        'type' => $field['type'],
                        'group' => $group,
                        'label' => $field['label'],
                        'options' => $field['options'] ?? null,
                    ]
                );

                // پاک کردن کش این کلید و گروه
                $this->invalidateCache($key, $group);
            }
        });
    }

    /**
     * مدیریت آپلود فایل و حذف فایل قدیمی (بدون پکیج)
     */
    private function handleFileUpload(string $key, UploadedFile $file, string $type): string
    {
        // ۱. دریافت مسیر فایل قدیمی برای حذف
        $oldPath = Setting::where('key', $key)->value('value');

        // ۲. حذف فایل قدیمی اگر وجود داشته باشد
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // ۳. ذخیره فایل جدید (لاراول به صورت خودکار نام یکتا تولید می‌کند)
        // اگر نیاز به تغییر سایز دارید، اینجا می‌توانید از GD یا Imagick PHP استفاده کنید
        return $file->store('settings', 'public');
    }

    /**
     * پاکسازی هوشمند کش
     */
    private function invalidateCache(string $key, string $group): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::CACHE_PREFIX . 'group_' . $group);
        Cache::forget(self::CACHE_PREFIX . 'all');
    }

    /**
     * تبدیل نوع داده
     */
    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'json', 'select' => json_decode($value, true),
            'boolean' => (bool) $value,
            default => (string) $value,
        };
    }

    private function getDefaultFromConfig(string $key): mixed
    {
        foreach (config('settings') as $group) {
            if (isset($group['fields'][$key])) {
                return $group['fields'][$key]['default'] ?? null;
            }
        }
        return null;
    }
}
