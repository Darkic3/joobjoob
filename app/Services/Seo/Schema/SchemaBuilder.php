<?php

namespace App\Services\Seo\Schema;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Seo\Breadcrumbs;
use App\Services\Seo\SeoResolver;
use Illuminate\Support\Str;

/**
 * تولید دادهٔ ساختاریافته (JSON-LD) صفحهٔ جاری.
 *
 * خروجی یک `@graph` است (روش پیشنهادی گوگل): موجودیت‌های مشترک مثل Organization
 * و WebSite یک بار اعلام و با `@id` ارجاع داده می‌شوند، به‌جای تکرار در هر بلوک.
 *
 * ⚠️ نکتهٔ حیاتی امنیت/اعتبار: هیچ مقداری با string interpolation داخل JSON
 * نوشته نمی‌شود. خروجی همیشه با `json_encode` ساخته می‌شود (در ویو)، چون نام
 * محصول یا مقاله می‌تواند `"` یا `<` داشته باشد و JSON را بشکند.
 *
 * معماری: یک کلاس منسجم با متدهای خصوصی به‌ازای هر نوع، به‌جای ۹ کلاس تک‌متدی —
 * تا کد قابل مرور بماند و انتزاع بی‌مصرف ساخته نشود.
 */
class SchemaBuilder
{
    /** حداکثر آیتم فهرست در ItemList — بیشتر از این، ارزش سئویی ندارد */
    private const MAX_LIST_ITEMS = 30;

    /** حداکثر نظر برای اسکیمای محصول */
    private const MAX_REVIEWS = 5;

    /**
     * نتیجهٔ رزولوشن، یک بار برای همین صفحه.
     * بدون این کش، هر ارجاع به canonical/description یک زنجیرهٔ کامل + کوئری تکرار می‌کرد.
     *
     * @var array<string, mixed>|null
     */
    private ?array $resolved = null;

    public function __construct(
        private readonly SeoResolver $resolver,
        private readonly Breadcrumbs $breadcrumbs,
    ) {}

    /**
     * گراف کامل صفحهٔ جاری.
     *
     * @return array<int, array<string, mixed>>
     */
    public function build(): array
    {
        $route = $this->baseRouteName();
        $model = seo()->model();
        $canonical = $this->canonical();
        $graph = [];

        // موجودیت‌های سراسری — در همهٔ صفحه‌ها اعلام می‌شوند تا قابل ارجاع باشند
        $graph[] = $this->organization();

        if ($route === 'home') {
            $graph[] = $this->website();
        }

        if ($route === 'products.show' && $model instanceof Product) {
            $graph[] = $this->product($model, $canonical);
        }


        if ($route === 'blogs.show' && $model instanceof Blog) {
            $graph[] = $this->article($model, $canonical);
        }

        if ($this->isCollectionRoute($route)) {
            $collection = $this->collectionPage($route, $canonical);

            if ($collection !== null) {
                $graph[] = $collection;
            }

            $itemList = $this->itemList($canonical);

            if ($itemList !== null) {
                $graph[] = $itemList;
            }
        }

        $staticPage = $this->staticPage($route, $canonical);

        if ($staticPage !== null) {
            $graph[] = $staticPage;
        }

        $breadcrumbs = $this->breadcrumbList();

        if ($breadcrumbs !== null) {
            $graph[] = $breadcrumbs;
        }

        // JSON-LD دستی اضافه‌شده توسط کنترلر/ویو
        foreach (seo()->schemas() as $custom) {
            $graph[] = $custom;
        }

        return array_values(array_filter($graph));
    }

    // ==========================================
    // موجودیت‌های سراسری
    // ==========================================

    /** @return array<string, mixed> */
    private function organization(): array
    {
        $siteName = $this->siteName();
        $home = localizedRoute('home');

        $organization = [
            '@type' => 'Organization',
            '@id' => $home.'#organization',
            'name' => $siteName,
            'url' => $home,
        ];

        $logo = $this->absoluteImage(setting('logo'));

        if ($logo !== null) {
            $organization['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
        }

        $sameAs = $this->socialProfiles();

        if ($sameAs !== []) {
            $organization['sameAs'] = $sameAs;
        }

        $contactPoint = $this->contactPoint();

        if ($contactPoint !== null) {
            $organization['contactPoint'] = [$contactPoint];
        }

        return $organization;
    }

    /** @return array<string, mixed> */
    private function website(): array
    {
        $home = localizedRoute('home');

        return [
            '@type' => 'WebSite',
            '@id' => $home.'#website',
            'url' => $home,
            'name' => $this->siteName(),
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@id' => $home.'#organization'],
            // جستجوی داخلی سایت برای نمایش sitelinks searchbox در نتایج گوگل
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => localizedRoute('products.index').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    // ==========================================
    // صفحهٔ محصول
    // ==========================================

    /** @return array<string, mixed>|null */
    private function product(Product $product, string $canonical): ?array
    {
        $name = $product->trans('name');

        if (! filled($name)) {
            return null;
        }

        [$currency, $multiplier] = $this->priceCurrency();

        $offer = [
            '@type' => 'Offer',
            'url' => $canonical,
            'priceCurrency' => $currency,
            'price' => (string) ((int) $product->final_price * $multiplier),
            'availability' => ((int) $product->stock > 0)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
        ];

        $validUntil = $this->priceValidUntil($product);

        if ($validUntil !== null) {
            $offer['priceValidUntil'] = $validUntil;
        }

        $schema = [
            '@type' => 'Product',
            '@id' => $canonical.'#product',
            'name' => $name,
            'url' => $canonical,
            'offers' => $offer,
        ];

        $description = $this->resolverDescription();

        if ($description !== null) {
            $schema['description'] = $description;
        }

        if (filled($product->sku)) {
            $schema['sku'] = (string) $product->sku;
        }

        $image = $this->absoluteImage($product->main_image_url);

        if ($image !== null) {
            $schema['image'] = [$image];
        }

        $brand = $product->relationLoaded('brand') ? $product->brand : null;

        if ($brand !== null && filled($brand->trans('title'))) {
            $schema['brand'] = ['@type' => 'Brand', 'name' => $brand->trans('title')];
        }

        $category = $product->relationLoaded('category') ? $product->category : null;

        if ($category !== null && filled($category->trans('name'))) {
            $schema['category'] = $category->trans('name');
        }

        $reviewCount = (int) $product->reviews_count;

        // aggregateRating فقط با دادهٔ واقعی — امتیاز ساختگی نقض دستورالعمل گوگل است
        if ($reviewCount > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $product->average_rating,
                'reviewCount' => (string) $reviewCount,
                'bestRating' => '5',
                'worstRating' => '1',
            ];

            $reviews = $this->reviews($product);

            if ($reviews !== []) {
                $schema['review'] = $reviews;
            }
        }

        return $schema;
    }

    /** @return array<int, array<string, mixed>> */
    private function reviews(Product $product): array
    {
        if (! $product->relationLoaded('reviews')) {
            return [];
        }

        return $product->reviews
            ->take(self::MAX_REVIEWS)
            ->map(function ($review): array {
                $item = [
                    '@type' => 'Review',
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => (string) $review->rating,
                        'bestRating' => '5',
                        'worstRating' => '1',
                    ],
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review->user?->name ?? $review->guest_name ?? $this->siteName(),
                    ],
                ];

                if ($review->created_at !== null) {
                    $item['datePublished'] = $review->created_at->toIso8601String();
                }

                $comment = trim((string) $review->comment);

                if ($comment !== '') {
                    $item['reviewBody'] = Str::limit(strip_tags($comment), 300, '');
                }

                return $item;
            })
            ->values()
            ->all();
    }

    // ==========================================
    // صفحهٔ مقاله
    // ==========================================

    /** @return array<string, mixed>|null */
    private function article(Blog $blog, string $canonical): ?array
    {
        if (! filled($blog->title)) {
            return null;
        }

        $author = $blog->relationLoaded('author') ? $blog->author?->name : null;

        $schema = [
            '@type' => 'BlogPosting',
            '@id' => $canonical.'#article',
            'headline' => Str::limit($blog->title, 110, ''),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
            'url' => $canonical,
            'inLanguage' => app()->getLocale(),
            'author' => [
                '@type' => $author !== null ? 'Person' : 'Organization',
                'name' => $author ?? $this->siteName(),
            ],
            'publisher' => ['@id' => localizedRoute('home').'#organization'],
        ];

        $excerpt = trim((string) $blog->excerpt);

        if ($excerpt !== '') {
            $schema['description'] = Str::limit(strip_tags($excerpt), 200, '');
        }

        $image = $this->absoluteImage($blog->image_url);

        if ($image !== null) {
            $schema['image'] = [$image];
        }

        if ($blog->created_at !== null) {
            $schema['datePublished'] = $blog->created_at->toIso8601String();
        }

        if ($blog->updated_at !== null) {
            $schema['dateModified'] = $blog->updated_at->toIso8601String();
        }

        // شمارش واژه با الگوی پروژه (Blog::readingMinutes) — str_word_count روی UTF-8
        // فارسی درست کار نمی‌کند و عدد غلط می‌دهد.
        $words = count(preg_split('/\s+/u', trim(strip_tags((string) $blog->content)), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if ($words > 0) {
            $schema['wordCount'] = $words;
        }

        return $schema;
    }

    // ==========================================
    // صفحه‌های فهرست
    // ==========================================

    private function isCollectionRoute(string $route): bool
    {
        return in_array($route, [
            'products.index',
            'products.category',
            'blogs.index',
            'blogs.category',
        ], true);
    }

    /** @return array<string, mixed>|null */
    private function collectionPage(string $route, string $canonical): ?array
    {
        $name = match ($route) {
            'products.index' => (string) __('messages.products'),
            'blogs.index' => (string) __('messages.blogs'),
            default => $this->breadcrumbLabel(),
        };

        $model = seo()->model();

        if (! filled($name) && $model !== null) {
            $name = $model instanceof ProductCategory
                ? (string) ($model->trans('name') ?? '')
                : (string) ($model instanceof BlogCategory ? $model->title : '');
        }

        return array_filter([
            '@type' => 'CollectionPage',
            '@id' => $canonical.'#collection',
            'name' => $name !== '' ? $name : null,
            'url' => $canonical,
            'inLanguage' => app()->getLocale(),
        ]);
    }

    /** @return array<string, mixed>|null */
    private function itemList(string $canonical): ?array
    {
        $items = array_slice(seo()->collectionItems(), 0, self::MAX_LIST_ITEMS);

        if ($items === []) {
            return null;
        }

        $elements = [];
        $offset = seo()->listOffset();

        foreach (array_values($items) as $index => $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));

            if ($name === '' || $url === '') {
                continue;
            }

            $elements[] = [
                '@type' => 'ListItem',
                // موقعیت در کل مجموعه — با احتساب آیتم‌های صفحه‌های قبل (صفحه‌بندی)
                'position' => $offset + $index + 1,
                'url' => $url,
                'name' => $name,
            ];
        }

        if ($elements === []) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            '@id' => $canonical.'#itemlist',
            'itemListElement' => $elements,
        ];
    }

    // ==========================================
    // صفحه‌های استاتیک
    // ==========================================

    /** @return array<string, mixed>|null */
    private function staticPage(string $route, string $canonical): ?array
    {
        if ($route !== 'site.page') {
            return null;
        }

        $page = (string) request()->route()?->parameter('page');

        $base = [
            '@id' => $canonical.'#webpage',
            'name' => $this->breadcrumbLabel(),
            'url' => $canonical,
            'inLanguage' => app()->getLocale(),
        ];

        return match ($page) {
            'contact' => array_merge($base, $this->contactPageSchema()),
            'faq' => array_merge($base, $this->faqSchema()),
            'about' => array_merge(['@type' => 'AboutPage'], $base),
            default => array_merge(['@type' => 'WebPage'], $base),
        };
    }

    /** @return array<string, mixed> */
    private function contactPageSchema(): array
    {
        $schema = ['@type' => 'ContactPage'];

        $contactPoint = $this->contactPoint();

        if ($contactPoint !== null) {
            $schema['contactPoint'] = [$contactPoint];
        }

        return $schema;
    }

    /** @return array<string, mixed> */
    private function faqSchema(): array
    {
        $items = (array) trans('messages.pages.faq.items');

        $entities = [];

        foreach ($items as $item) {
            $question = trim((string) ($item['q'] ?? ''));
            $answer = trim((string) ($item['a'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
            ];
        }

        // بدون پرسش‌وپاسخ معتبر، FAQPage بی‌معنا است ⇒ به WebPage تنزل می‌کند
        if ($entities === []) {
            return ['@type' => 'WebPage'];
        }

        return ['@type' => 'FAQPage', 'mainEntity' => $entities];
    }

    // ==========================================
    // Breadcrumb
    // ==========================================

    /** @return array<string, mixed>|null */
    private function breadcrumbList(): ?array
    {
        $items = $this->breadcrumbs->items();

        if (count($items) < 2) {
            return null;
        }

        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
            ];

            // آخرین آیتم URL ندارد (صفحهٔ جاری) — گوگل هم همین را می‌پذیرد
            if ($item['url'] !== null) {
                $element['item'] = $item['url'];
            }

            $elements[] = $element;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->canonical().'#breadcrumb',
            'itemListElement' => $elements,
        ];
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /** رزولوشن متای صفحه — یک بار محاسبه و کش می‌شود */
    private function resolveSeo(): array
    {
        return $this->resolved ??= $this->resolver->resolve();
    }

    private function canonical(): string
    {
        return (string) ($this->resolveSeo()['canonical'] ?? localizedUrl());
    }

    private function siteName(): string
    {
        return (string) setting('site_name', config('app.name', 'Joobjoob'));
    }

    /** توضیح صفحه — همان مقداری که به‌عنوان meta description رندر می‌شود */
    private function resolverDescription(): ?string
    {
        $description = (string) ($this->resolveSeo()['description'] ?? '');
        $description = trim($description);

        return $description === '' ? null : $description;
    }

    /** @return array{0: string, 1: int} [currency, multiplier] */
    private function priceCurrency(): array
    {
        $currency = (string) setting('seo_schema_currency', config('seo.schema_currency', 'IRR'));
        $multipliers = (array) config('seo.schema_currency_multiplier', []);

        return [$currency, (int) ($multipliers[$currency] ?? 1)];
    }

    private function priceValidUntil(Product $product): ?string
    {
        if ($product->timer_active && $product->end_timer !== null && $product->end_timer->isFuture()) {
            return $product->end_timer->toDateString();
        }

        if ($product->deal_ends_at !== null && $product->deal_ends_at->isFuture()) {
            return $product->deal_ends_at->toDateString();
        }

        return null;
    }

    private function absoluteImage(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_match('~^(https?:)?//~i', $value) ? $value : url($value);
    }

    /** @return array<int, string> */
    private function socialProfiles(): array
    {
        $profiles = [];

        foreach (['social_instagram', 'social_telegram', 'social_whatsapp', 'social_twitter', 'social_youtube'] as $key) {
            $url = trim((string) setting($key));

            if ($url !== '' && preg_match('~^https?://~i', $url)) {
                $profiles[] = $url;
            }
        }

        return $profiles;
    }

    /** @return array<string, mixed>|null */
    private function contactPoint(): ?array
    {
        $phone = trim((string) setting('phone'));

        if ($phone === '') {
            return null;
        }

        $languages = array_keys(locale()->active());

        return array_filter([
            '@type' => 'ContactPoint',
            'telephone' => $phone,
            'contactType' => 'customer support',
            'availableLanguage' => $languages !== [] ? array_values($languages) : null,
        ]);
    }

    /** آخرین عنوان مسیر راهنما — برای نام صفحه در اسکیمای WebPage */
    private function breadcrumbLabel(): string
    {
        $items = $this->breadcrumbs->items();

        return (string) ($items[count($items) - 1]['label'] ?? $this->siteName());
    }

    private function baseRouteName(): string
    {
        return (string) preg_replace('/^locale\./', '', (string) request()->route()?->getName());
    }
}
