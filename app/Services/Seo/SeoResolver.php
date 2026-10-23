<?php

namespace App\Services\Seo;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * محاسبهٔ مقادیر نهایی متای SEO یک صفحه.
 *
 * زنجیرهٔ اولویت (نزولی — هر مرحله که مقدار بدهد، مراحل پایین‌تر نادیده می‌مانند):
 *   ۱) override صریح کنترلر/ویو (`seo()->title(...)`)
 *   ۲) سیاست سخت مسیر/پارامتر (config/seo.php) — فقط برای نوایندکس‌کردن
 *   ۳) ردیف `seo_meta` (اول entity، بعد path) برای زبان جاری
 *   ۴) `@section('title')` ویوهای موجود (سازگاری کامل با کد فعلی)
 *   ۵) مقدار مشتق‌شده از مدل (name/title/description/تصویر)
 *   ۶) تنظیمات ادمین (`setting('seo_...')`)
 *   ۷) `config('seo.*')`
 *
 * چرا سیاست مسیر **بالاتر** از ردیف دیتابیس است: این سیاست یک قاعدهٔ معماری است
 * (سبد خرید و حساب کاربری هرگز نباید ایندکس شوند). اگر ردیف دیتابیس بالاتر بود،
 * یک اشتباه ادمین می‌توانست صفحهٔ خصوصی را ایندکس‌پذیر کند.
 */
class SeoResolver
{
    /** حداکثر طول توضیح متا — فراتر از آن گوگل برش می‌زند */
    private const DESCRIPTION_LIMIT = 160;

    public function __construct(private readonly SeoManager $manager) {}

    // ==========================================
    // API عمومی
    // ==========================================

    /**
     * سیاست سختِ مسیر و پارامترهای query — بدون هیچ کوئری دیتابیس.
     *
     * جدا از resolve() است تا میدل‌ور هدرها (فاز ۴) بتواند پیش از اجرای کنترلر
     * و بدون هزینهٔ کوئری، تصمیم noindex را بگیرد.
     *
     * @return array{indexable: bool, followable: bool}|null  null ⇒ سیاستی اعمال نشد
     */
    public function routePolicy(Request $request): ?array
    {
        if ($this->routeIsNoIndex($request)) {
            return ['indexable' => false, 'followable' => false];
        }

        // نتیجهٔ جستجوی داخلی محتوای یکتا نیست ⇒ noindex ولی لینک‌ها دنبال شوند
        foreach ((array) config('seo.noindex_query_params', []) as $param) {
            if ($request->filled($param)) {
                return ['indexable' => false, 'followable' => true];
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function resolve(): array
    {
        $request = request();
        $locale = app()->getLocale();
        $model = $this->manager->model();
        $overrides = $this->manager->overrides();

        $siteName = $this->siteName();
        $meta = $this->metaRow($model, $locale);

        // ── عنوان ──
        $title = $this->applyTitleTemplate(
            (string) ($overrides['title']
                ?? $meta?->title
                ?? $this->sectionTitle()
                ?? $this->modelTitle($model)
                ?? $siteName),
            $siteName
        );

        // ── توضیحات ──
        $description = $this->firstFilled(
            $overrides['description'] ?? null,
            $meta?->description,
            $this->modelDescription($model, $locale),
            setting('seo_default_description', config('seo.default_description'))
        ) ?? '';

        // ── keywords (اختیاری — گوگل نادیده می‌گیرد ولی برخی موتورها می‌خوانند) ──
        $keywords = $this->firstFilled(
            $overrides['keywords'] ?? null,
            $meta?->keywords,
            setting('seo_default_keywords', config('seo.default_keywords'))
        );

        // ── canonical ──
        $canonical = $this->resolveCanonical($request, $overrides['canonical_url'] ?? $meta?->canonical_url);

        // ── robots ──
        [$indexable, $followable] = $this->resolveRobots($request, $overrides, $meta, $model, $locale);

        // ── تصویر ──
        $image = $this->resolveImage($overrides['og_image'] ?? null, $meta?->og_image, $model);

        // ── Open Graph ──
        $ogTitle = $this->firstFilled($overrides['og_title'] ?? null, $meta?->og_title) ?? $title;
        $ogDescription = $this->firstFilled($overrides['og_description'] ?? null, $meta?->og_description) ?? $description;

        $og = [
            'type' => $this->firstFilled(
                $overrides['og_type'] ?? null,
                $meta?->og_type,
                $this->defaultOgType($request)
            ),
            'url' => $this->firstFilled($overrides['og_url'] ?? null, $meta?->og_url !== null ? $this->absoluteUrl((string) $meta->og_url) : null) ?? $canonical,
            'title' => $ogTitle,
            'description' => $ogDescription,
            'image' => $image,
            'site_name' => $siteName,
            'locale' => ogLocale($locale),
            // همهٔ زبان‌های فعال **دیگر** — نه فقط زبان پیش‌فرض.
            // (باگ قبلی: روی صفحهٔ fa، خودِ fa به‌عنوان alternate تکرار می‌شد.)
            'locale_alternates' => $this->ogLocaleAlternates($locale),
        ];

        // ── Twitter Card ──
        $twitter = [
            'card' => $this->firstFilled(
                $overrides['twitter_card'] ?? null,
                $meta?->twitter_card,
                setting('seo_twitter_card', config('seo.default_twitter_card'))
            ),
            'site' => $this->firstFilled(
                $overrides['twitter_site'] ?? null,
                $meta?->twitter_site,
                setting('seo_twitter_site', config('seo.twitter_site'))
            ),
            'creator' => $this->firstFilled(
                $overrides['twitter_creator'] ?? null,
                $meta?->twitter_creator,
                setting('seo_twitter_creator', config('seo.twitter_creator'))
            ),
            'title' => $this->firstFilled($overrides['twitter_title'] ?? null, $meta?->twitter_title) ?? $ogTitle,
            'description' => $this->firstFilled($overrides['twitter_description'] ?? null, $meta?->twitter_description) ?? $ogDescription,
            'image' => $this->resolveImage(
                $overrides['twitter_image'] ?? null,
                $meta?->twitter_image,
                null // تصویر مدل قبلاً برای OG حل شده؛ همین را بازاستفاده می‌کنیم
            ) ?? $image,
        ];

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'robots' => ($indexable ? 'index' : 'noindex').', '.($followable ? 'follow' : 'nofollow'),
            'is_indexable' => $indexable,
            'is_followable' => $followable,
            'og' => $og,
            'twitter' => $twitter,
            'alternates' => $this->alternateUrls(),
            'x_default' => localizedUrl(locale()->defaultCode()),
            // توجه: JSON-LD اینجا تولید نمی‌شود — SchemaBuilder تنها منبع گراف است
            // تا دادهٔ ساختاریافته دو منبع حقیقت نداشته باشد.
        ];
    }

    // ==========================================
    // عنوان و توضیحات
    // ==========================================

    private function siteName(): string
    {
        return (string) setting('site_name', config('app.name', 'Joobjoob'));
    }

    /**
     * عنوانی که ویو با `@section('title', ...)` اعلام کرده است.
     * سازگاری کامل با ویوهای فعلی که همچنان از این روش استفاده می‌کنند.
     */
    private function sectionTitle(): ?string
    {
        $title = trim(strip_tags((string) View::getSection('title', '')));

        return $title === '' ? null : $title;
    }

    /**
     * اعمال قالب عنوان. اگر عنوان از قبل نام سایت را داشته باشد، پسوند دوباره
     * اضافه نمی‌شود تا عنوان‌هایی مثل «فروشگاه جوب‌جوب - خانه» به
     * «فروشگاه جوب‌جوب - خانه | فروشگاه جوب‌جوب» تبدیل نشوند.
     */
    private function applyTitleTemplate(string $title, string $siteName): string
    {
        $title = trim(strip_tags($title));

        if ($title === '') {
            $title = $siteName;
        }

        if ($title === '') {
            return '';
        }

        $template = (string) setting('seo_title_template', config('seo.title_template', '{title}'));

        if ($siteName !== '' && Str::contains($title, $siteName)) {
            return $title;
        }

        $rendered = str_replace(['{title}', '{site_name}'], [$title, $siteName], $template);

        return trim($rendered) !== '' ? trim($rendered) : $title;
    }

    private function modelTitle(?Model $model): ?string
    {
        if ($model === null) {
            return null;
        }

        foreach ((array) config('seo.translatable_fields', ['name', 'title']) as $field) {
            if (! array_key_exists($field, $model->getAttributes())) {
                continue;
            }

            if (method_exists($model, 'trans')) {
                $value = $model->trans($field);
            } else {
                $value = $model->getAttribute($field);
                if (is_array($value)) {
                    $value = $value[app()->getLocale()] ?? $value['fa'] ?? null;
                }
            }

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function modelDescription(?Model $model, string $locale): ?string
    {
        if ($model === null) {
            return null;
        }

        // ترتیب اولویت: توضیح کوتاه (مناسب متا) → خلاصهٔ مقاله → توضیح کامل
        foreach (['short_description', 'excerpt', 'description'] as $field) {
            if (! array_key_exists($field, $model->getAttributes())) {
                continue;
            }

            $value = $model->getAttribute($field);

            if (is_array($value)) {
                $value = $value[$locale] ?? $value['fa'] ?? null;
            }

            $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)) ?? '');

            if ($value !== '') {
                return Str::limit($value, self::DESCRIPTION_LIMIT, '');
            }
        }

        return null;
    }

    // ==========================================
    // canonical
    // ==========================================

    private function resolveCanonical(Request $request, ?string $explicit): string
    {
        if ($explicit !== null && trim($explicit) !== '') {
            return $this->absoluteUrl(trim($explicit));
        }

        $url = localizedUrl();

        // فقط پارامترهای «صفحه‌بندی» در canonical می‌مانند (self-canonical).
        // فیلتر/مرتب‌سازی/جستجو حذف می‌شوند تا به صفحهٔ تمیز ادغام شوند.
        $keep = [];

        foreach ((array) config('seo.canonical_keep_query_params', []) as $param) {
            if ($request->filled($param)) {
                $keep[$param] = $request->query($param);
            }
        }

        if ($keep !== []) {
            $url .= '?'.http_build_query($keep);
        }

        return $url;
    }

    /** مسیر یا URL نسبی را مطلق می‌کند (og:image و canonical همیشه مطلق لازم‌اند) */
    private function absoluteUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        return preg_match('~^(https?:)?//~i', $value) ? $value : url($value);
    }

    // ==========================================
    // robots
    // ==========================================

    /**
     * @return array{0: bool, 1: bool}  [indexable, followable]
     */
    private function resolveRobots(
        Request $request,
        array $overrides,
        ?SeoMeta $meta,
        ?Model $model,
        string $locale
    ): array {
        // ۱) override صریح کنترلر
        if (array_key_exists('is_indexable', $overrides) || array_key_exists('is_followable', $overrides)) {
            return [
                $this->toBool($overrides['is_indexable'] ?? true),
                $this->toBool($overrides['is_followable'] ?? true),
            ];
        }

        // ۲) سیاست سخت مسیر/پارامتر
        $policy = $this->routePolicy($request);

        if ($policy !== null) {
            return [$policy['indexable'], $policy['followable']];
        }

        // ۳) ردیف متای ذخیره‌شده
        if ($meta !== null) {
            return [$meta->is_indexable, $meta->is_followable];
        }

        // ۴) سیاست ترجمهٔ ناقص
        if ($model !== null && ! $this->hasTranslation($model, $locale)) {
            $translationPolicy = (string) setting(
                'seo_missing_translation_policy',
                config('seo.missing_translation_policy', 'noindex')
            );

            if ($translationPolicy === 'noindex') {
                return [false, true];
            }
        }

        // ۵) پیش‌فرض
        return [true, true];
    }

    private function routeIsNoIndex(Request $request): bool
    {
        $name = $request->route()?->getName();

        // مسیر بدون نام روت (صفحهٔ خطا/۴۰۴) ⇒ نوایندکس
        if ($name === null) {
            return true;
        }

        // روت‌های زبان‌دار نامشان locale.* است ⇒ به نام پایه برگردان
        $base = (string) preg_replace('/^locale\./', '', $name);

        if (in_array($base, (array) config('seo.noindex_route_names', []), true)) {
            return true;
        }

        foreach ((array) config('seo.noindex_route_patterns', []) as $pattern) {
            if (Str::is($pattern, $base)) {
                return true;
            }
        }

        return false;
    }

    /**
     * آیا این مدل برای زبان خواسته‌شده ترجمه دارد؟
     *
     * `trans()` در مدل‌ها به fa فلاوبک می‌کند؛ یعنی `/en/product-x` می‌تواند محتوای
     * فارسی نشان بدهد در حالی که hreflang ادعای en دارد ⇒ محتوای تکراری چندزبانه.
     * فیلد غیرآرایه (مثل title رشته‌ای بلاگ) ترجمه‌پذیر نیست و «دارد» حساب می‌شود.
     */
    public function hasTranslation(Model $model, string $locale): bool
    {
        foreach ((array) config('seo.translatable_fields', ['name', 'title']) as $field) {
            if (! array_key_exists($field, $model->getAttributes())) {
                continue;
            }

            $value = $model->getAttribute($field);

            if (! is_array($value)) {
                // فیلد ساده (ترجمه‌ناپذیر) یا انتخاب‌نشده ⇒ محافظه‌کارانه «دارد»
                return true;
            }

            return filled($value[$locale] ?? null);
        }

        return true;
    }

    // ==========================================
    // ردیف متای ذخیره‌شده
    // ==========================================

    /**
     * ردیف متای زبان جاری: اول entity (اگر صفحه مدل دارد)، بعد ردیف path-based.
     */
    private function metaRow(?Model $model, string $locale): ?SeoMeta
    {
        if ($model !== null && method_exists($model, 'seoMetaFor')) {
            $row = $model->seoMetaFor($locale);

            if ($row !== null) {
                return $row;
            }
        }

        return SeoMeta::query()
            ->forPath($this->currentPath())
            ->forLocale($locale)
            ->first();
    }

    /**
     * مسیر جاری بدون پیشوند زبان — چون ردیف‌های path-based برای همهٔ زبان‌ها
     * با همان مسیر بدون پیشوند ذخیره می‌شوند.
     */
    private function currentPath(): string
    {
        if ($this->manager->path() !== null) {
            return SeoMeta::normalizePath($this->manager->path());
        }

        $segments = explode('/', trim((string) request()->path(), '/'));

        if ($segments !== [] && $segments[0] !== '' && in_array($segments[0], locale()->codes(), true)) {
            array_shift($segments);
        }

        return SeoMeta::normalizePath(implode('/', $segments));
    }

    // ==========================================
    // تصویر
    // ==========================================

    private function resolveImage(?string $explicit, ?string $metaImage, ?Model $model): ?string
    {
        $candidate = $this->firstFilled(
            $explicit,
            $metaImage,
            $this->modelImage($model),
            setting('seo_default_og_image', config('seo.default_og_image')),
            setting('logo')
        );

        return $candidate === null ? null : $this->absoluteUrl($candidate);
    }

    private function modelImage(?Model $model): ?string
    {
        if ($model === null) {
            return null;
        }

        // نام‌های اکسسور تصویر در مدل‌های مختلف پروژه
        foreach (['main_image_url', 'image_url', 'item_image'] as $accessor) {
            $value = $model->getAttribute($accessor);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    // ==========================================
    // چندزبانه
    // ==========================================

    /** @return array<string, string> کد زبان ⇒ URL همان صفحه در آن زبان (برای hreflang) */
    private function alternateUrls(): array
    {
        $urls = [];

        foreach (array_keys(locale()->active()) as $code) {
            $urls[$code] = localizedUrl($code);
        }

        return $urls;
    }

    /**
     * زبان‌های فعال **به‌جز** زبان جاری، برای `og:locale:alternate`.
     *
     * @return array<int, string>
     */
    private function ogLocaleAlternates(string $current): array
    {
        $alternates = [];

        foreach (array_keys(locale()->active()) as $code) {
            if ($code !== $current) {
                $alternates[] = ogLocale($code);
            }
        }

        return $alternates;
    }

    private function defaultOgType(Request $request): string
    {
        $base = (string) preg_replace('/^locale\./', '', (string) $request->route()?->getName());

        return $base === 'products.show'
            ? 'product'
            : (string) config('seo.default_og_type', 'website');
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /** اولین مقدار غیرتهی از میان آرگومان‌ها */
    private function firstFilled(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    private function toBool(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
