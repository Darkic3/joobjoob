<?php

namespace App\Services\Seo;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\View;

/**
 * منبع واحد مسیر راهنما (breadcrumb) برای صفحهٔ جاری.
 *
 * هر دو مصرف‌کننده — کامپوننت بصری `<x-breadcrumbs />` و اسکیمای
 * `BreadcrumbList` — از همین سرویس می‌خوانند تا هرگز با هم اختلاف نداشته باشند
 * (اختلاف بین breadcrumb بصری و ساختاریافته یکی از دلایل رایج هشدار گوگل است).
 *
 * قرارداد: آخرین آیتم صفحهٔ جاری است و `url` آن `null` می‌شود.
 */
class Breadcrumbs
{
    /** محافظ در برابر حلقهٔ احتمالی در درخت دسته‌بندی */
    private const MAX_DEPTH = 10;

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    public function items(): array
    {
        $route = $this->baseRouteName();
        $model = seo()->model();

        return match (true) {
            $route === 'home' => [
                ['label' => $this->homeLabel(), 'url' => null],
            ],

            $route === 'products.index' => [
                $this->link($this->homeLabel(), localizedRoute('home')),
                ['label' => __('messages.products'), 'url' => null],
            ],

            $route === 'products.category' && $model instanceof ProductCategory => $this->productCategoryTrail($model),

            $route === 'products.show' && $model instanceof Product => $this->productTrail($model),

            $route === 'blogs.index' => [
                $this->link($this->homeLabel(), localizedRoute('home')),
                ['label' => __('messages.blogs'), 'url' => null],
            ],

            $route === 'blogs.category' && $model instanceof BlogCategory => $this->blogCategoryTrail($model),

            $route === 'blogs.show' && $model instanceof Blog => $this->blogTrail($model),

            $route === 'site.page' => [
                $this->link($this->homeLabel(), localizedRoute('home')),
                ['label' => $this->pageLabel(), 'url' => null],
            ],

            default => [
                $this->link($this->homeLabel(), localizedRoute('home')),
            ],
        };
    }

    // ==========================================
    // مسیرهای اختصاصی
    // ==========================================

    /** @return array<int, array{label: string, url: string|null}> */
    private function productCategoryTrail(ProductCategory $category): array
    {
        $trail = [
            $this->link($this->homeLabel(), localizedRoute('home')),
            $this->link(__('messages.products'), localizedRoute('products.index')),
        ];

        $chain = $this->ancestorChain($category);
        $lastIndex = count($chain) - 1;

        foreach ($chain as $index => $node) {
            $trail[] = [
                'label' => $node->trans('name') ?? $node->slug,
                'url' => $index === $lastIndex ? null : localizedRoute('products.category', ['category' => $node->slug]),
            ];
        }

        return $trail;
    }

    /** @return array<int, array{label: string, url: string|null}> */
    private function productTrail(Product $product): array
    {
        $trail = [
            $this->link($this->homeLabel(), localizedRoute('home')),
            $this->link(__('messages.products'), localizedRoute('products.index')),
        ];

        $category = $product->relationLoaded('category') ? $product->category : null;

        if ($category !== null) {
            $chain = $this->ancestorChain($category);

            foreach ($chain as $node) {
                $trail[] = $this->link(
                    $node->trans('name') ?? $node->slug,
                    localizedRoute('products.category', ['category' => $node->slug])
                );
            }
        }

        $trail[] = ['label' => $product->trans('name') ?? $product->slug, 'url' => null];

        return $trail;
    }

    /** @return array<int, array{label: string, url: string|null}> */
    private function blogCategoryTrail(BlogCategory $category): array
    {
        $trail = [
            $this->link($this->homeLabel(), localizedRoute('home')),
            $this->link(__('messages.blogs'), localizedRoute('blogs.index')),
        ];

        $chain = $this->ancestorChain($category);

        foreach ($chain as $index => $node) {
            $trail[] = [
                'label' => $node->title,
                'url' => $index === count($chain) - 1 ? null : localizedRoute('blogs.category', ['category' => $node->slug]),
            ];
        }

        return $trail;
    }

    /** @return array<int, array{label: string, url: string|null}> */
    private function blogTrail(Blog $blog): array
    {
        $trail = [
            $this->link($this->homeLabel(), localizedRoute('home')),
            $this->link(__('messages.blogs'), localizedRoute('blogs.index')),
        ];

        $category = $blog->relationLoaded('category') ? $blog->category : null;

        if ($category !== null) {
            $trail[] = $this->link($category->title, localizedRoute('blogs.category', ['category' => $category->slug]));
        }

        $trail[] = ['label' => $blog->title, 'url' => null];

        return $trail;
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /**
     * زنجیرهٔ نیاکان از ریشه تا خودِ گره (والد → فرزند).
     *
     * @return array<int, ProductCategory|BlogCategory>
     */
    private function ancestorChain(ProductCategory|BlogCategory $node): array
    {
        $chain = [];
        $current = $node;
        $depth = 0;

        while ($current !== null && $depth < self::MAX_DEPTH) {
            array_unshift($chain, $current);
            $current = $current->parent;
            $depth++;
        }

        return $chain;
    }

    /** @return array{label: string, url: string|null} */
    private function link(string $label, string $url): array
    {
        return ['label' => $label, 'url' => $url];
    }

    private function homeLabel(): string
    {
        return (string) __('messages.home');
    }

    /** برچسب صفحهٔ استاتیک — از خود ویو (که عنوان را از فایل ترجمه می‌گیرد) */
    private function pageLabel(): string
    {
        return (string) View::getSection('title', __('messages.site_name'));
    }

    /** نام روت پایه — بدون پیشوند locale. */
    private function baseRouteName(): string
    {
        return (string) preg_replace('/^locale\./', '', (string) request()->route()?->getName());
    }
}
