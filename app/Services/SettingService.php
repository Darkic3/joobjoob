<?php

namespace App\Services;

use App\Helpers\ImageHandler;
use App\Helpers\Uploader;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    /**
     * کلید کش یکپارچه تمام تنظیمات (به‌جای یک کش جدا برای هر کلید)
     */
    private const CACHE_ALL = 'shopora_settings_all';

    /**
     * حافظه‌ی درخواست — همه‌ی تنظیمات فقط یک بار با یک کوئری لود می‌شوند
     * و تا پایان ریکوئست از حافظه خوانده می‌شوند (بدون کوئری تکراری)
     *
     * @var array<string, mixed>|null
     */
    private static ?array $memory = null;

    /**
     * دریافت مقدار یک تنظیم (از حافظه/کش یکپارچه، سپس دیفالت کانفیگ و آرگومان)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        // اگر در دیتابیس نبود، مقدار پیش‌فرض را از کانفیگ بخوان
        return $this->getDefaultFromConfig($key) ?? $default;
    }

    /**
     * تمام تنظیمات — یک کوئری در هر ریکوئست + کش یکپارچه بین ریکوئست‌ها
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (self::$memory !== null) {
            return self::$memory;
        }

        return self::$memory = Cache::rememberForever(self::CACHE_ALL, function () {
            return Setting::all()
                ->mapWithKeys(fn (Setting $setting) => [
                    $setting->key => $this->castValue($setting->value, $setting->type),
                ])
                ->all();
        });
    }

    /**
     * مقدار تنظیم فقط اگر ادمین صریحاً ذخیره کرده باشد (و خالی نباشد)؛
     * در غیر این صورت مقدار پیش‌فرض آرگومان برگردانده می‌شود.
     *
     * برخلاف get() که دیفالت کانفیگ را هم برمی‌گرداند، اینجا دیفالت کانفیگ نادیده گرفته می‌شود —
     * مناسب متن‌هایی که باید به زبان جاری ترجمه شوند (مثل عنوان بخش‌های صفحه اصلی).
     */
    public function getStored(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * فیلدهای یک گروه خاص از کانفیگ (فیلدها به‌صورت تخت با کلید tab تعریف شده‌اند)
     */
    public function fieldsForGroup(string $group): array
    {
        $configFields = config('settings.fields', []);

        return array_filter($configFields, fn ($field) => ($field['tab'] ?? 'general') === $group);
    }

    /**
     * دریافت تمام تنظیمات یک گروه خاص — از حافظه/کش یکپارچه + دیفالت‌های کانفیگ
     *
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        $all = $this->all();
        $settings = [];

        foreach ($this->fieldsForGroup($group) as $key => $field) {
            $settings[$key] = $all[$key] ?? ($field['default'] ?? null);
        }

        return $settings;
    }

    /**
     * به‌روزرسانی تنظیمات (با مدیریت فایل و تراکنش)
     */
    public function updateGroup(string $group, array $data, array $files = []): void
    {
        DB::transaction(function () use ($group, $data, $files) {
            $configFields = $this->fieldsForGroup($group);

            foreach ($configFields as $key => $field) {
                $isFileType = in_array($field['type'] ?? 'text', ['file', 'video'], true);
                $value = $data[$key] ?? null;
                $file = $files[$key] ?? null;

                // اگر فایل آپلود شده باشد
                if ($file instanceof UploadedFile) {
                    $value = $this->handleFileUpload($key, $file, $field['type']);
                } elseif ($isFileType) {
                    // فیلد فایل بدون آپلود جدید → مقدار قبلی حفظ شود (از بین نرود)
                    $value = Setting::where('key', $key)->value('value');
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
            }
        });

        // بعد از اتمام موفق تراکنش، کش و حافظه را پاک کن
        $this->flush();
    }

    /**
     * پاک‌سازی کش یکپارچه و حافظه‌ی درخواست
     */
    public function flush(): void
    {
        self::$memory = null;
        Cache::forget(self::CACHE_ALL);
    }

    /**
     * مدیریت آپلود فایل و حذف فایل قدیمی با هلپرهای اختصاصی شما
     */
    private function handleFileUpload(string $key, UploadedFile $file, string $type): string
    {
        // ۱. دریافت مسیر فایل قدیمی
        $oldPath = Setting::where('key', $key)->value('value');

        // ۲. ذخیره فایل جدید — تصاویر با ImageHandler (تبدیل WebP) و بقیه با Uploader
        //    اول آپلود جدید انجام می‌شود؛ فقط اگر موفق بود فایل قدیمی حذف می‌شود
        if (str_starts_with($file->getMimeType() ?: '', 'image/')) {
            $newPath = ImageHandler::handle('settings', $file);
        } else {
            $newPath = Uploader::upload($file, config('paths.upload', 'uploads').'/settings');
        }

        if (! $newPath) {
            return (string) ($oldPath ?? '');
        }

        // ۳. حذف فایل قدیمی اگر وجود داشته باشد
        if ($oldPath) {
            ImageHandler::delete('settings', $oldPath);
        }

        return $newPath;
    }

    /**
     * تبدیل نوع داده
     */
    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'json' => json_decode((string) $value, true) ?? [],
            // سلکت تک‌مقداری: مقدار ذخیره‌شده همان کلید گزینهٔ انتخابی است
            'select' => (string) $value,
            'boolean' => (bool) $value,
            default => (string) $value,
        };
    }

    private function getDefaultFromConfig(string $key): mixed
    {
        return config("settings.fields.{$key}.default");
    }
}
