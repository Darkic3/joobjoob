<?php

namespace App\Services\Seo;

use Illuminate\Database\Eloquent\Model;

/**
 * جمع‌کنندهٔ overrideهای صریح SEO در طول یک درخواست.
 *
 * کنترلرها و ویوها با `seo()->title(...)` مقدار قطعی خودشان را اعلام می‌کنند؛
 * `SeoResolver` این مقادیر را بالاترین اولویت زنجیرهٔ fallback می‌داند.
 *
 * ⚠️ طراحی مهم — چرا state روی Request و نه روی خودِ سرویس:
 * این سرویس singleton است تا `seo()` در کنترلر و کامپوننت `<x-seo-head />` به
 * **یک** نمونه برسند. اگر state را در پراپرتی نگه می‌داشتیم، در محیط‌هایی که
 * چند درخواست در یک پروسه اجرا می‌شوند (تست‌ها، queue worker، Octane) مقادیر
 * درخواست قبلی به درخواست بعدی نشت می‌کرد و مثلاً عنوان صفحهٔ محصول روی صفحهٔ
 * بعدی می‌ماند. با نگه‌داشتن state روی `$request->attributes`، هر درخواست
 * ظرف مستقل خودش را دارد و نیازی به reset دستی نیست.
 */
class SeoManager
{
    private const ATTRIBUTE = 'shopora_seo_state';

    /** کلیدهای متای پیش‌فرض که override می‌پذیرند */
    private const ALLOWED_KEYS = [
        'title', 'description', 'keywords', 'focus_keyword',
        'canonical_url', 'is_indexable', 'is_followable',
        'og_title', 'og_description', 'og_image', 'og_type', 'og_url',
        'twitter_card', 'twitter_site', 'twitter_creator',
        'twitter_title', 'twitter_description', 'twitter_image',
        'schema_type',
    ];

    /**
     * @return array{overrides: array<string, mixed>, model: Model|null, path: string|null, schemas: array<int, array>}
     */
    private function bag(): array
    {
        $bag = request()->attributes->get(self::ATTRIBUTE);

        if (! is_array($bag)) {
            $bag = [];
        }

        return $bag + ['overrides' => [], 'model' => null, 'path' => null, 'schemas' => [], 'list' => [], 'list_offset' => 0];
    }

    private function put(array $bag): void
    {
        request()->attributes->set(self::ATTRIBUTE, $bag);
    }

    // ==========================================
    // API عمومی
    // ==========================================

    /** یک مقدار متا را قطعی کن */
    public function set(string $key, mixed $value): static
    {
        if (! in_array($key, self::ALLOWED_KEYS, true)) {
            return $this;
        }

        $bag = $this->bag();
        $bag['overrides'][$key] = $value;
        $this->put($bag);

        return $this;
    }

    /** چند مقدار متا را یک‌جا قطعی کن */
    public function setMany(array $data): static
    {
        foreach ($data as $key => $value) {
            $this->set((string) $key, $value);
        }

        return $this;
    }

    public function title(?string $title): static
    {
        return $this->set('title', $title);
    }

    public function description(?string $description): static
    {
        return $this->set('description', $description);
    }

    public function image(?string $url): static
    {
        return $this->set('og_image', $url);
    }

    public function canonical(?string $url): static
    {
        return $this->set('canonical_url', $url);
    }

    /** ترکیب راحت برای صفحه‌های غیرقابل‌ایندکس */
    public function noIndex(bool $nofollow = true): static
    {
        return $this->set('is_indexable', false)->set('is_followable', ! $nofollow);
    }

    /** موجودیت این صفحه — منبع متای اختصاصی و مقادیر مشتق‌شده (متقارن با forPath) */
    public function forModel(?Model $model): static
    {
        $bag = $this->bag();
        $bag['model'] = $model;
        $this->put($bag);

        return $this;
    }

    /** مسیر canonical این صفحه (وقتی صفحه مدل ندارد، مثل /products) */
    public function forPath(?string $path): static
    {
        $bag = $this->bag();
        $bag['path'] = $path;
        $this->put($bag);

        return $this;
    }

    /** افزودن JSON-LD دستی (فاز ۳) */
    public function addSchema(array $schema): static
    {
        $bag = $this->bag();
        $bag['schemas'][] = $schema;
        $this->put($bag);

        return $this;
    }

    /**
     * آیتمهای فهرست صفحه (برای اسکیمای ItemList).
     * صفحه‌های فهرست/دسته‌بندی این را با نام و URL آیتمهای همان صفحه پر می‌کنند.
     *
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public function listItems(array $items, int $offset = 0): static
    {
        $bag = $this->bag();
        $bag['list'] = $items;
        $bag['list_offset'] = max(0, $offset);
        $this->put($bag);

        return $this;
    }

    // ==========================================
    // خواندن
    // ==========================================

    /** @return array<string, mixed> */
    public function overrides(): array
    {
        return $this->bag()['overrides'];
    }

    public function override(string $key): mixed
    {
        return $this->bag()['overrides'][$key] ?? null;
    }

    public function model(): ?Model
    {
        return $this->bag()['model'];
    }

    public function path(): ?string
    {
        return $this->bag()['path'];
    }

    /** @return array<int, array> */
    public function schemas(): array
    {
        return $this->bag()['schemas'];
    }

    /** @return array<int, array{name: string, url: string}> */
    public function collectionItems(): array
    {
        return $this->bag()['list'];
    }

    /** تعداد آیتم‌های صفحه‌های قبل از صفحهٔ جاری — برای شماره‌گذاری ItemList */
    public function listOffset(): int
    {
        return (int) $this->bag()['list_offset'];
    }
}
