<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HeroSlide extends Model
{
    protected $fillable = [
        'badge', 'title', 'desc', 'btn_text',
        'linkable_type', 'linkable_id', 'image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'badge' => 'array',
        'title' => 'array',
        'desc' => 'array',
        'btn_text' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        // linkable برای fallback تصویر (وقتی اسلاید تصویری ندارد، از مدل لینک‌شده می‌گیرد)
        return $query->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * دریافت امن مدل لینک‌شده — فقط برای انواع واقعی مدل‌ها (Product, Category, Blog و...)
     * برای مقادیر غیرکلاسی مثل 'url' و 'page' null برمی‌گرداند.
     */
    public function linkableModel(): ?Model
    {
        if (! $this->linkable_type || in_array($this->linkable_type, ['url', 'page'])) {
            return null;
        }

        return $this->linkable;
    }

    public function trans(string $field): string
    {
        $value = $this->{$field};
        if (! is_array($value) || empty($value)) {
            return '';
        }
        $currentLocale = app()->getLocale();

        // مقادیر null داخل آرایه (مثلاً title = ['fa' => null]) نباید null برگردانند
        return (string) ($value[$currentLocale] ?? $value['fa'] ?? reset($value) ?? '');
    }

    // اکسسور برای دریافت لینک نهایی اسلاید
    public function getUrlAttribute(): string
    {
        // اگر لینک دستی (URL) وارد شده باشد
        if ($this->linkable_type === 'url') {
            return $this->linkable_id ?? '#';
        }

        // اگر صفحه استاتیک باشد (contact / about / faq / terms / privacy)
        if ($this->linkable_type === 'page') {
            return localizedRoute('site.page', ['page' => $this->linkable_id]);
        }

        // اگر به یک مدل (محصول، دسته، مقاله) لینک شده باشد — روت واقعی سمت سایت
        if ($this->linkable_type && $this->linkable_id && $this->linkable) {
            $model = $this->linkable;

            return match (true) {
                $model instanceof Product => localizedRoute('products.show', ['product' => $model->slug]),
                $model instanceof ProductCategory => localizedRoute('products.category', ['category' => $model->slug]),
                $model instanceof Blog => localizedRoute('blogs.show', ['blog' => $model->slug]),
                $model instanceof BlogCategory => localizedRoute('blogs.category', ['category' => $model->slug]),
                default => '#',
            };
        }

        return '#';
    }

    // بررسی آیا دکمه فعال است
    public function hasButton(): bool
    {
        return ! empty($this->btn_text['fa']) || ! empty($this->linkable_type);
    }

    /**
     * نوع مقصد لینک در فرم ادمین (برای پیش‌انتخاب دراپ‌داون).
     */
    public function linkType(): string
    {
        if (! $this->linkable_type) {
            return 'custom';
        }

        return match (true) {
            $this->linkable_type === 'url' => 'custom',
            str_contains($this->linkable_type, 'Product') && ! str_contains($this->linkable_type, 'Category') => 'product',
            str_contains($this->linkable_type, 'ProductCategory') => 'product_category',
            str_contains($this->linkable_type, 'Blog') && ! str_contains($this->linkable_type, 'Category') => 'blog',
            str_contains($this->linkable_type, 'BlogCategory') => 'blog_category',
            str_contains($this->linkable_type, 'Page') => 'page',
            default => 'custom',
        };
    }

    /**
     * آدرس تصویر اسلاید — لینک خارجی، مسیر آپلود محلی، یا تصویر مدل لینک‌شده
     * (اگر اسلاید تصویری ندارد ولی به محصول/دسته/مقاله لینک شده، تصویر همان مدل استفاده می‌شود).
     */
    public function imageUrl(): string
    {
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        if (! empty($this->image)) {
            return asset(config('paths.upload', 'uploads').'/hero-slides/'.$this->image);
        }

        return $this->linkedModelImage() ?? '';
    }

    /**
     * تصویر مدل لینک‌شده (محصول → تصویر اصلی، دسته/مقاله → تصویر شاخص).
     */
    private function linkedModelImage(): ?string
    {
        $model = $this->linkableModel();
        if (! $model) {
            return null;
        }

        $url = method_exists($model, 'getMainImageUrlAttribute') ? $model->main_image_url : null;
        if (! $url && method_exists($model, 'getImageUrlAttribute')) {
            $url = $model->image_url;
        }

        return $url ?: null;
    }

    /**
     * دادهٔ مدل لینک‌شده به فرمت نتایج جستجو (id/title/subtitle/image)
     * تا موقع ادیت، مدل به‌صورت خودکار در فرم انتخاب و پر شده باشد.
     *
     * @return array{id: int, title: string, subtitle: string, image: ?string}|null
     */
    public function linkableFormSeed(): ?array
    {
        $model = $this->linkableModel();
        if (! $model) {
            return null;
        }

        $title = $this->linkedModelTitle($model);
        if ($title === '') {
            return null;
        }

        $image = method_exists($model, 'getMainImageUrlAttribute')
            ? $model->main_image_url
            : (method_exists($model, 'getImageUrlAttribute') ? $model->image_url : null);

        return [
            'id' => $model->getKey(),
            'title' => $title,
            'subtitle' => $model instanceof Product
                ? number_format($model->final_price).' تومان'
                : '',
            'image' => $image,
        ];
    }

    /**
     * عنوان نمایشی مدل لینک‌شده (فیلد ترجمه‌ای یا رشته‌ای).
     */
    private function linkedModelTitle(Model $model): string
    {
        if (method_exists($model, 'trans')) {
            $title = $model->trans('name') ?? $model->trans('title');
            if ($title) {
                return (string) $title;
            }
        }

        return (string) ($model->title ?? $model->name ?? '');
    }
}
