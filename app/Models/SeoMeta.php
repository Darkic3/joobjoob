<?php

namespace App\Models;

use App\Services\Seo\SitemapBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * متای SEO یک هدف (entity یا مسیر) در یک زبان مشخص.
 *
 * منبع واحد حقیقت برای title/description/canonical/robots/OG/Twitter/schema/sitemap.
 * نبودِ ردیف برای یک زبان یعنی «از زنجیرهٔ fallback استفاده کن»، نه «متا نداریم».
 *
 * قرارداد `path`: همیشه مسیری نرمال‌شده و شروع‌شده با «/» است (ریشه = «/»)،
 * بدون پیشوند زبان — چون canonical هر زبان مسیر بدون پیشوند است.
 * مثال: `/`, `/products`, `/blogs`, `/page/faq`
 */
class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    /** مقادیر مجاز changefreq در sitemap */
    public const CHANGEFREQS = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

    /** مقادیر مجاز twitter:card */
    public const TWITTER_CARDS = ['summary', 'summary_large_image', 'app', 'player'];

    /** پیش‌فرض نوع OG برای صفحات غیرمحصول */
    public const DEFAULT_OG_TYPE = 'website';

    protected $fillable = [
        'seo_metable_type',
        'seo_metable_id',
        'path',
        'locale',
        'is_indexable',
        'is_followable',
        'title',
        'description',
        'keywords',
        'focus_keyword',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'og_url',
        'twitter_card',
        'twitter_site',
        'twitter_creator',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_type',
        'schema_json',
        'sitemap_priority',
        'sitemap_changefreq',
    ];

    /**
     * هر تغییر متا (noindex‌کردن، تغییر priority یا canonical) باید sitemap را
     * بازسازی کند — پس کش sitemap همین‌جا پاک می‌شود، نه در کنترلر پنل.
     * مزیت: هر مسیر دیگری که متا را ورق بزند (یمنارتی/Seeder) هم خودکار پوشش می‌گیرد.
     */
    protected static function booted(): void
    {
        static::saved(fn () => SitemapBuilder::forget());
        static::deleted(fn () => SitemapBuilder::forget());
    }

    protected $casts = [
        'is_indexable' => 'boolean',
        'is_followable' => 'boolean',
        'schema_json' => 'array',
        'sitemap_priority' => 'float',
    ];

    // ==========================================
    // روابط
    // ==========================================

    /** هدف متا وقتی یک entity است (محصول، دسته‌بندی، مقاله، برند) */
    public function seo_metable(): MorphTo
    {
        return $this->morphTo();
    }

    // ==========================================
    // اسکوپ‌ها
    // ==========================================

    public function scopeForLocale(Builder $query, ?string $locale = null): Builder
    {
        return $query->where('locale', $locale ?? app()->getLocale());
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('is_indexable', true);
    }

    public function scopeForPath(Builder $query, string $path): Builder
    {
        return $query->where('path', self::normalizePath($path));
    }

    // ==========================================
    // اکسسورها / کمک‌کننده‌ها
    // ==========================================

    /**
     * نرمال‌سازی مسیر — تا مقایسهٔ رزولور همیشه قطعی باشد:
     *   'products'      → '/products'
     *   '/products/'    → '/products'
     *   '/' , '' , null → '/'
     */
    public static function normalizePath(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        // اگر کسی آدرس کامل وارد کرد، فقط مسیرش را نگه می‌داریم
        if (preg_match('~^https?://~i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = '/'.ltrim($path, '/');

        return rtrim($path, '/') ?: '/';
    }

    /** مسیر را همیشه نرمال ذخیره کن */
    public function setPathAttribute(?string $value): void
    {
        $this->attributes['path'] = $value === null ? null : self::normalizePath($value);
    }

    /** مقدار آمادهٔ تگ `<meta name="robots">` */
    public function robots(): string
    {
        return ($this->is_indexable ? 'index' : 'noindex')
            .', '
            .($this->is_followable ? 'follow' : 'nofollow');
    }

    /** آیا این ردیف هیچ مقدار محتوایی ندارد (فقط پرچم‌های پیش‌فرض)؟ */
    public function hasNoContent(): bool
    {
        $contentKeys = [
            'title', 'description', 'keywords', 'focus_keyword', 'canonical_url',
            'og_title', 'og_description', 'og_image', 'og_type', 'og_url',
            'twitter_card', 'twitter_site', 'twitter_creator', 'twitter_title',
            'twitter_description', 'twitter_image',
            'schema_type', 'schema_json', 'sitemap_priority', 'sitemap_changefreq',
        ];

        foreach ($contentKeys as $key) {
            $value = $this->getAttribute($key);

            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    /** آیا این ردیف دقیقاً معادل «بدون تنظیم خاص» است و می‌توان حذفش کرد؟ */
    public function isRedundant(): bool
    {
        return $this->hasNoContent() && $this->is_indexable === true && $this->is_followable === true;
    }

    /** برچسب انسانی هدف — برای لیست‌های پنل */
    public function targetLabel(): string
    {
        return $this->path !== null
            ? $this->path
            : ($this->seo_metable_type ?? '—').' #'.$this->seo_metable_id;
    }
}
