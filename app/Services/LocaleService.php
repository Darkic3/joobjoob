<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

/**
 * منبع واحد حقیقت زبان‌ها در runtime.
 *
 * الگوی کش دقیقاً مثل SettingService بهینه‌شده است:
 *  - حافظه‌ی ریکوئست: همه‌ی زبان‌ها فقط یک بار با یک کوئری لود می‌شوند
 *  - کش یکپارچه بین ریکوئست‌ها (به‌جای کش جدا برای هر کلید)
 *  - flush() روی هر ذخیره/حذف زبان
 *
 * مصرف‌کننده‌ها فقط از این سرویس می‌خوانند — نه از آرایه‌های منسوخ
 * کانفیگ (available_locales/rtl_locales حذف شده‌اند).
 */
class LocaleService
{
    private const CACHE_ALL = 'joobjoob_languages_all';

    /**
     * حافظه‌ی ریکوئست — همه‌ی زبان‌ها keyed by code.
     *
     * @var array<string, Language>|null
     */
    private static ?array $memory = null;

    /**
     * همه‌ی زبان‌ها (فعال + غیرفعال) — یک کوئری + کش یکپارچه.
     *
     * @return array<string, Language>
     */
    public function all(): array
    {
        if (self::$memory !== null) {
            return self::$memory;
        }

        try {
            return self::$memory = Cache::rememberForever(self::CACHE_ALL, function () {
                return \App\Models\Language::ordered()->get()->keyBy('code')->all();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return [];
        }
    }

    /**
     * فقط زبان‌های فعال (به ترتیب sort_order).
     *
     * @return array<string, Language>
     */
    public function active(): array
    {
        return array_filter($this->all(), fn (Language $language) => $language->is_active);
    }

    /** @return array<int, string> */
    public function codes(): array
    {
        return array_keys($this->all());
    }

    /** @return array<int, string> */
    public function activeCodes(): array
    {
        return array_keys($this->active());
    }

    public function has(string $code): bool
    {
        return isset($this->all()[$code]);
    }

    public function find(string $code): ?Language
    {
        return $this->all()[$code] ?? null;
    }

    public function isActive(string $code): bool
    {
        $language = $this->all()[$code] ?? null;

        return $language !== null && $language->is_active;
    }

    /**
     * زبان پیش‌فرض — دفاعی:
     *  ۱) زبانِ پیش‌فرضِ فعال
     *  ۲) اولین زبان فعال
     *  ۳) هر زبانِ پیش‌فرض (حتی غیرفعال — حالت دادهٔ خراب)
     *  ۴) null (سیدر اجباری است)
     */
    public function default(): ?Language
    {
        // ۱) پیش‌فرضِ فعال
        foreach ($this->all() as $language) {
            if ($language->is_default && $language->is_active) {
                return $language;
            }
        }

        // ۲) اولین زبان فعال
        $actives = $this->active();

        if ($actives) {
            return reset($actives);
        }

        // ۳) هر زبانِ پیش‌فرض (دادهٔ خراب: پیش‌فرض غیرفعال)
        foreach ($this->all() as $language) {
            if ($language->is_default) {
                return $language;
            }
        }

        return null;
    }

    public function defaultCode(): string
    {
        return $this->default()?->code ?? config('app.locale', 'fa');
    }

    /**
     * جهت صفحه برای یک زبان: ردیف دیتابیس → کاتالوگ → _default.
     */
    public function dir(string $code): string
    {
        $language = $this->all()[$code] ?? null;

        return $language?->dir
            ?? config("languages.{$code}.dir", config('languages._default.dir', 'ltr'));
    }

    public function isRtl(string $code): bool
    {
        return $this->dir($code) === 'rtl';
    }

    /**
     * خانواده‌ی تقویم: jalali | hijri | gregorian (از کاتالوگ).
     *
     *   - jalali → شمسی (fa و هر زبانِ شمسیِ ثبت‌شده) — jdate_format
     *   - hijri  → قمری (ar و هر زبانِ قمریِ ثبت‌شده) — hijri_date
     *   - gregorian → میلادی (پیش‌فرض) — Carbon::translatedFormat
     */
    public function calendar(string $code): string
    {
        return config("languages.{$code}.calendar", config('languages._default.calendar', 'gregorian'));
    }

    public function isJalali(string $code): bool
    {
        return $this->calendar($code) === 'jalali';
    }

    public function isHijri(string $code): bool
    {
        return $this->calendar($code) === 'hijri';
    }

    /**
     * خانواده‌ی ارقام: persian | arabic | latin (از کاتالوگ).
     */
    public function digitFamily(string $code): string
    {
        return config("languages.{$code}.digits", config('languages._default.digits', 'latin'));
    }

    /**
     * حالت تک‌زبانه: کمتر از ۲ زبان فعال → سوییچر مخفی و ?lang نادیده.
     */
    public function singleLanguageMode(): bool
    {
        return count($this->active()) <= 1;
    }

    /**
     * بعد از هر ذخیره/حذف زبان — کش یکپارچه و حافظه‌ی ریکوئست را پاک کن.
     */
    public function flush(): void
    {
        self::$memory = null;
        Cache::forget(self::CACHE_ALL);
    }
}
