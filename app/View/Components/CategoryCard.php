<?php

namespace App\View\Components;

use App\Models\ProductCategory;
use Illuminate\View\Component;
use Illuminate\View\View;

class CategoryCard extends Component
{
    public string $name;

    public string $url;

    public int $count;

    public string $icon;

    public string $countLabel;

    /** نگاشت کلیدهای آیکون متنی به ایموجی (آیکون‌های نام‌دار قدیمی) */
    private const ICON_MAP = [
        'shirt-outline' => '👕',
        'glasses-outline' => '👓',
        'footsteps-outline' => '🧦',
        'phone-outline' => '📱',
        'laptop-outline' => '💻',
        'home-outline' => '🏠',
        'book-outline' => '📚',
        'sports-outline' => '⚽',
        'car-outline' => '🚗',
        'heart-outline' => '💄',
    ];

    public function __construct(public ProductCategory $category, public ?int $index = null)
    {
        $this->name = $category->trans('name') ?: __('messages.categories');
        $this->url = localizedRoute('products.index', ['category' => $category->slug]);
        $this->count = (int) ($category->products_count ?? 0);
        $this->icon = $this->resolveIcon();
        $this->countLabel = $this->resolveCountLabel();
    }

    public function render(): View
    {
        return view('components.category-card');
    }

    /**
     * اگر آیکون یک نماد/ایموجی واقعی باشد (تک‌کاراکتری یا شامل کاراکتر غیر-ASCII)
     * مستقیم نمایش داده می‌شود؛ کلیدهای نام‌دار انگلیسی از نگاشت به ایموجی تبدیل می‌شوند.
     */
    private function resolveIcon(): string
    {
        $storedIcon = trim((string) ($this->category->icon ?? ''));

        if ($storedIcon === '') {
            return '📦';
        }

        $isSymbol = mb_strlen($storedIcon) === 1 || ! preg_match('/^[a-zA-Z0-9_-]+$/', $storedIcon);

        return $isSymbol ? $storedIcon : (self::ICON_MAP[$storedIcon] ?? '📦');
    }

    /**
     * برچسب تعداد محصولات: فارسی و ترکی بعد از عدد همیشه مفرد می‌گیرند («۲ محصول» / «2 Ürün»)؛
     * انگلیسی و عربی برای بیش از یک، جمع.
     */
    private function resolveCountLabel(): string
    {
        return in_array(app()->getLocale(), ['fa', 'tr'], true)
            ? __('messages.product')
            : ($this->count === 1 ? __('messages.product') : __('messages.products'));
    }
}
