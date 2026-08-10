<?php

namespace App\DTOs;

use App\Http\Requests\Admin\HeroSlideRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;

readonly class HeroSlideDTO
{
    public function __construct(
        public array $title,
        public array $desc,
        public array $badge,
        public bool $button_active,
        public array $btn_text,
        public ?string $link_type,
        public ?string $link_value,
        public ?int $price,
        public ?int $old_price,
        public string $currency,
        public bool $rating_active,
        public ?string $rating,
        public bool $viewers_active,
        public ?int $viewers,
        public array $trusts,
        public ?UploadedFile $image,
        public bool $is_active,
    ) {}

    public static function fromRequest(HeroSlideRequest $request): self
    {
        $buttonActive = $request->boolean('button_active');
        $linkType = $buttonActive ? $request->input('link_type') : null;
        $linkValue = $buttonActive ? $request->input('link_value') : null;

        $ratingActive = $request->boolean('rating_active');
        $viewersActive = $request->boolean('viewers_active');

        return new self(
            title: ['fa' => $request->input('title.fa', ''), 'en' => $request->input('title.en', '')],
            desc: ['fa' => $request->input('desc.fa', ''), 'en' => $request->input('desc.en', '')],
            badge: ['fa' => $request->input('badge.fa', ''), 'en' => $request->input('badge.en', '')],
            button_active: $buttonActive,
            btn_text: ['fa' => $request->input('btn_text.fa', ''), 'en' => $request->input('btn_text.en', '')],
            link_type: $linkType,
            link_value: $linkValue,
            price: $request->integer('price', 0),
            old_price: $request->integer('old_price', 0),
            currency: $request->input('currency', 'IRT'),
            rating_active: $ratingActive,
            rating: $ratingActive ? $request->input('rating') : null,
            viewers_active: $viewersActive,
            viewers: $viewersActive ? $request->integer('viewers', 0) : null,
            trusts: self::normalizeTrusts((array) $request->input('trusts', [])),
            image: $request->file('image'),
            is_active: $request->boolean('is_active'),
        );
    }

    /**
     * نرمال‌سازی آیتم‌های اعتماد: فقط آیتم‌های دارای متن پذیرفته می‌شوند،
     * هر آیتم به شکل {icon, text: {fa, en}} ذخیره می‌شود و حداکثر ۵ آیتم.
     */
    private static function normalizeTrusts(array $raw): array
    {
        $trusts = [];

        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $fa = trim((string) ($item['fa'] ?? ''));
            $en = trim((string) ($item['en'] ?? ''));
            if ($fa === '' && $en === '') {
                continue;
            }

            $icon = trim((string) ($item['icon'] ?? '✓'));
            $trusts[] = [
                'icon' => $icon !== '' ? $icon : '✓',
                'text' => ['fa' => $fa, 'en' => $en],
            ];
        }

        return array_slice($trusts, 0, 5);
    }

    public function getLinkableType(): ?string
    {
        return match ($this->link_type) {
            'product' => Product::class,
            'product_category' => ProductCategory::class,
            'blog' => Blog::class,
            'blog_category' => BlogCategory::class,
            'page' => 'page',
            'custom' => 'url',
            default => null,
        };
    }

    public function getLinkableId(): ?string
    {
        // برای custom/page مقدار رشته‌ای (URL یا کلید صفحه) و برای مدل‌ها شناسهٔ مدل است
        return $this->link_value;
    }
}
