<?php

namespace App\Traits;

use App\Models\SeoMeta;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * اتصال یک entity به ردیف‌های متای SEO خودش (به‌ازای هر زبان).
 *
 * مصرف‌کننده‌ها:
 *   - پنل ادمین: `syncSeoMeta($request->validated()['seo'])`
 *   - رزولور رندر: `seoMetaFor($locale)`
 *
 * ⚠️ این trait هیچ ستون متایی روی جدول خودش اضافه نمی‌کند — همه‌چیز در `seo_meta`
 * نگه داشته می‌شود تا صفحات بدون مدل هم بتوانند متا داشته باشند.
 */
trait HasSeoMeta
{
    /**
     * پاک‌سازی کش sitemap با هر تغییر موجودیت.
     *
     * چرا در همین trait و نه در هر مدل: هر پنج مدل متادار از این trait استفاده
     * می‌کنند، پس یک نقطهٔ واحد داریم و مدل تازه‌ای که فراموش کند وجود ندارد.
     * اسلاگ، وضعیت فعال/غیرفعال و متن‌ها همگی مسیر یا ایندکس‌پذیری URL را عوض می‌کنند.
     */
    protected static function bootHasSeoMeta(): void
    {
        static::saved(fn () => SitemapBuilder::forget());
        static::deleted(fn () => SitemapBuilder::forget());
    }

    /** همهٔ ردیف‌های متای این entity (همهٔ زبان‌ها) */
    public function seoMeta(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'seo_metable');
    }

    /**
     * متای یک زبان مشخص (پیش‌فرض: زبان جاری).
     * اگر رابطه از قبل لود شده باشد، کوئری اضافه نمی‌زند.
     */
    public function seoMetaFor(?string $locale = null): ?SeoMeta
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('seoMeta')) {
            return $this->seoMeta->firstWhere('locale', $locale);
        }

        return $this->seoMeta()->where('locale', $locale)->first();
    }

    /**
     * ذخیره/به‌روزرسانی متای چندزبانه از ورودی پنل.
     *
     * شکل ورودی: ['fa' => ['title' => ...], 'en' => [...]]
     *
     * قواعد:
     *   - زبان‌های ناشناخته نادیده گرفته می‌شوند (جلوگیری از ردیف زائد از ورودی دست‌کاری‌شده)
     *   - هدف (morph) از رابطه تعیین می‌شود، نه از ورودی
     *   - ردیفی که هیچ محتوایی ندارد و فقط پرچم‌های پیش‌فرض را دارد ذخیره/حذف می‌شود
     *     تا جدول با ردیف‌های خالی پر نشود
     */
    public function syncSeoMeta(array $perLocale): void
    {
        foreach ($perLocale as $locale => $fields) {
            $locale = trim((string) $locale);

            if ($locale === '' || ! is_array($fields) || ! $this->isSupportedSeoLocale($locale)) {
                continue;
            }

            $data = $this->prepareSeoMetaData($fields);
            $existing = $this->seoMeta()->where('locale', $locale)->first();

            if ($existing !== null) {
                $existing->fill($data);

                if ($existing->isRedundant()) {
                    $existing->delete();
                } else {
                    $existing->save();
                }

                continue;
            }

            $candidate = new SeoMeta($data + ['locale' => $locale]);

            if ($candidate->isRedundant()) {
                continue;
            }

            $this->seoMeta()->save($candidate);
        }

        $this->unsetRelation('seoMeta');
    }

    /**
     * آماده‌سازی دادهٔ یک زبان برای ذخیره در SeoMeta.
     * فقط کلیدهای مجاز مدل عبور می‌کنند و رشتهٔ خالی به null تبدیل می‌شود
     * تا «خالی» و «مقدار نداشتن» در سراسر سیستم یک معنا داشته باشند.
     */
    private function prepareSeoMetaData(array $fields): array
    {
        $data = array_intersect_key($fields, array_flip((new SeoMeta())->getFillable()));

        // هدف و زبان از رابطه/حلقه تعیین می‌شوند، نه از ورودی کاربر
        unset($data['seo_metable_type'], $data['seo_metable_id'], $data['locale']);

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                $data[$key] = $value === '' ? null : $value;
            }
        }

        // پرچم ایندکس‌پذیری همیشه مقدار قطعی دارد.
        // مقدار خالی/نبود ⇒ پیش‌فرض روشن، تا یک فیلد جاافتاده ناخواسته سایت را نوایندکس نکند.
        $data['is_indexable'] = $this->toBoolFlag($fields['is_indexable'] ?? null, true);
        $data['is_followable'] = $this->toBoolFlag($fields['is_followable'] ?? null, true);

        return $data;
    }

    private function toBoolFlag(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * زبان پشتیبانی‌شده؟ — کاتالوگ config + جدول languages.
     * اگر هیچ‌کدام در دسترس نبود (مثلاً دیتابیس تازه بدون سیدر)، فقط قالب کد زبان بررسی می‌شود.
     */
    private function isSupportedSeoLocale(string $locale): bool
    {
        $allowed = array_values(array_diff(array_unique(array_merge(
            array_keys((array) config('languages', [])),
            locale()->codes(),
        )), ['_default']));

        if ($allowed !== []) {
            return in_array($locale, $allowed, true);
        }

        return (bool) preg_match('/^[a-z]{2}(-[A-Za-z]{2})?$/', $locale);
    }
}
