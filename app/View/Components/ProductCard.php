<?php

namespace App\View\Components;

use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\View\Component;
use Illuminate\View\View;

class ProductCard extends Component
{
    public string $name;

    public string $imageUrl;

    /** آیا محصول تصویر معتبری دارد؟ */
    public bool $hasImage;

    public string $url;

    public bool $hasDiscount;

    public int $discountPercent;

    public int $finalPrice;

    public int $originalPrice;

    public bool $lowStock;

    public bool $outOfStock;

    public bool $isNew;

    public float $rating;

    public int $reviewCount;

    /** آیا محصول الان در علاقه‌مندی‌هاست (برای حالت اولیهٔ قلب) */
    public bool $inWishlist;

    /** @var array{color: string, text: string}|null استایل نشان رتبه (فقط بخش پرفروش‌ترین‌ها) */
    public ?array $rankStyle;

    /** شناسهٔ کلکسیونی نمایشی — «برچسب انبار» تم Eco (مثل #ECO-0042) */
    public string $collectibleId;

    /** تگ متریال از روی مشخصات (null اگر مشخصات لود نشده یا متریال ندارد) */
    public ?string $materialTag;

    /**
     * تگ وضعیت کارت — اولویت: ناموجود → محدود → جدید → تخفیف.
     *
     * @var array{label: string, tone: string}|null
     */
    public ?array $statusTag;

    private const RANK_STYLES = [
        1 => ['color' => 'var(--gold)', 'text' => '#000'],
        2 => ['color' => 'var(--silver)', 'text' => '#000'],
        3 => ['color' => 'var(--bronze)', 'text' => '#fff'],
    ];

    public function __construct(
        public Product $product,
        public ?int $rank = null,
        public ?int $soldPercent = null,
    ) {
        $this->name = $product->trans('name');
        $this->imageUrl = $product->main_image_url ?? '';
        $this->hasImage = filled($this->imageUrl);
        $this->url = localizedRoute('products.show', ['product' => $product->slug]);

        $this->hasDiscount = $product->has_discount;
        $this->discountPercent = (int) ($product->discount_percentage ?? 0);
        $this->finalPrice = (int) $product->final_price;
        $this->originalPrice = (int) $product->price;

        $this->lowStock = $product->stock > 0 && $product->stock <= 5;
        $this->outOfStock = $product->stock === 0;

        // نشان جدید: محصولاتی که در ۱۴ روز اخیر اضافه شده‌اند
        $this->isNew = $product->created_at->diffInDays(now()) <= 14;

        // امتیاز و نظرات (با fallback ایمن)
        $this->rating = round($product->reviews->avg('rating') ?? 0, 1);
        $this->reviewCount = $product->reviews->count();

        $this->inWishlist = app(WishlistService::class)->has($product);

        $this->rankStyle = $rank ? (self::RANK_STYLES[$rank] ?? ['color' => 'var(--bg-elevated)', 'text' => 'var(--text-secondary)']) : null;

        // پراپرتی‌های نمایشی تم Eco-Collectible (منبع واحد: اکسسورهای مدل)
        $this->collectibleId = $product->collectible_id;
        $this->materialTag = $product->material_tag;
        $this->statusTag = $product->status_tag;
    }

    public function render(): View
    {
        return view('components.product-card');
    }
}
