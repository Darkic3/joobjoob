<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['images', 'category', 'brand'])->where('is_active', true);

        // ۱. فیلتر دسته‌بندی — شامل محصولات تمام زیرشاخه‌ها نیز می‌شود
        if ($request->filled('category')) {
            $slugs = is_array($request->category) ? $request->category : [$request->category];
            $query->whereIn('product_category_id', $this->categoryIdsWithDescendants($slugs));
        }

        // ۲. فیلتر قیمت (بر اساس final_price)
        if ($request->filled('min_price')) {
            $query->where('final_price', '>=', (int) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('final_price', '<=', (int) $request->max_price);
        }

        // ۳. فیلتر برند (بر اساس اسلاگ)
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('url', $request->brand));
        }

        // ۴. جستجو در نام محصول (پشتیبانی از JSON چندزبانه)
        if ($request->filled('q')) {
            $searchTerm = '%'.$request->q.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name->fa', 'like', $searchTerm)
                    ->orWhere('name->en', 'like', $searchTerm);
            });
        }

        // ۵. مرتب‌سازی (طبق گزارش: از final_price استفاده می‌شود)
        match ($request->get('sort')) {
            'price_asc' => $query->orderBy('final_price', 'asc'),
            'price_desc' => $query->orderBy('final_price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'best_selling' => $query->orderBy('is_best_seller', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('stock', 'desc')->orderBy('final_price', 'desc')->orderBy('id', 'desc'),
        };

        $products = $query->paginate(12)->withQueryString();

        // دریافت دسته‌بندی‌ها به همراه تعداد محصولات
        $categories = ProductCategory::where('is_active', true)
            ->withCount('products')
            ->get();

        // برندهای فعال
        $brands = Brand::where('active', true)->orderBy('id')->get();

        // دادهٔ فیلترهای فعلی برای حفظ حالت فرم‌ها (بدون منطق request در ویو)
        $filters = $request->except(['sort', 'page']);
        $selectedCategories = array_values(array_filter((array) ($request->input('category') ?? [])));
        $selectedBrand = $request->input('brand');
        $selectedSort = $request->input('sort', 'default');

        return view('site.products.index', compact('products', 'categories', 'brands', 'filters', 'selectedCategories', 'selectedBrand', 'selectedSort'));
    }

    /**
     * شناسه‌های دسته‌بندی + شناسه‌های تمام زیرشاخه‌ها (در هر عمقی) را برمی‌گرداند،
     * تا فیلتر دسته‌بندی، محصولات زیرشاخه‌ها را نیز شامل شود.
     */
    private function categoryIdsWithDescendants(array $slugs): array
    {
        $found = ProductCategory::whereIn('slug', $slugs)->pluck('id')->all();
        $all = $found;

        while ($found) {
            $found = ProductCategory::whereIn('parent_id', $found)->pluck('id')->all();
            $all = array_merge($all, $found);
        }

        return array_values(array_unique($all));
    }

    public function show(Product $product)
    {
        $product->load([
            'images',
            'category',
            'brand',
            'reviews.user',
            'productSpecifications.children.specification',
            'productSpecifications.specification',
            'variants.specifications.parent',
            'properties',
            'related.images',
            'complement.images',
        ]);

        // محصولات مرتبط: اگر دستی تعریف نشده باشد از همان دسته‌بندی
        $related = $product->related->isNotEmpty()
            ? $product->related
            : Product::where('product_category_id', $product->product_category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->take(7)
                ->get();

        $complement = $product->complement;

        // داده‌های نمایشی صفحهٔ محصول (مشخصات/واریانت‌ها/تصویر اصلی) — از ویو خارج شده
        $viewData = $this->prepareShowData($product);

        return view('site.products.show', array_merge(compact('product', 'related', 'complement'), $viewData));
    }

    /**
     * داده‌های نمایشی صفحهٔ جزئیات محصول را آماده می‌کند:
     * تصویر اصلی، گروه‌بندی مشخصات (والد/فرزند + مقادیر نوشتاری)،
     * ساختار واریانت برای انتخابگر و ارزان‌ترین واریانت موجود.
     *
     * @return array{mainImage: string, specGroups: array, textValues: array, mainSpecs: array, variantsData: \Illuminate\Support\Collection, minVariant: mixed}
     */
    private function prepareShowData(Product $product): array
    {
        $mainImage = $product->main_image_url ?? 'https://picsum.photos/seed/'.$product->slug.'/600/600';

        // ۱) مشخصه‌های انتخابی گروه‌بندی‌شده: والد → [مقادیر]
        $specGroups = [];
        foreach ($product->productSpecifications as $ps) {
            if ($ps->value === null && $ps->parent_id === null && $ps->specification) {
                $group = $ps->specification;
                $values = $ps->children->map(fn ($c) => $c->specification?->trans('title'))->filter()->implode('، ');
                $specGroups[$group->id] = ['title' => $group->trans('title'), 'values' => $values];
            }
        }

        // ۲) مقادیر نوشتاری
        $textValues = [];
        foreach ($product->productSpecifications as $ps) {
            if ($ps->value !== null && $ps->specification) {
                $textValues[] = ['title' => $ps->specification->trans('title'), 'value' => $ps->value];
            }
        }

        // ۳) ساختار واریانت برای انتخابگر
        // main_specs: [{id, title, is_color, children: [{id, title, color_code, variant_ids}]}]
        $mainSpecs = [];
        $variants = $product->variants;
        foreach ($variants as $variant) {
            foreach ($variant->specifications as $spec) {
                if (! $spec->parent) {
                    continue;
                }
                $groupId = $spec->parent->id;
                if (! isset($mainSpecs[$groupId])) {
                    $mainSpecs[$groupId] = [
                        'id' => $groupId,
                        'title' => $spec->parent->trans('title'),
                        'is_color' => (bool) $spec->parent->is_color,
                        'children' => [],
                    ];
                }
                $childKey = $spec->id;
                if (! isset($mainSpecs[$groupId]['children'][$childKey])) {
                    $mainSpecs[$groupId]['children'][$childKey] = [
                        'id' => $spec->id,
                        'title' => $spec->trans('title'),
                        'color_code' => $spec->color_code,
                        'variant_ids' => [],
                    ];
                }
                $mainSpecs[$groupId]['children'][$childKey]['variant_ids'][] = $variant->id;
            }
        }
        $mainSpecs = array_values(array_map(fn ($g) => [
            'id' => $g['id'],
            'title' => $g['title'],
            'is_color' => $g['is_color'],
            'children' => array_values($g['children']),
        ], $mainSpecs));

        // واریانت‌ها با specification_ids و قیمت برای JS
        $variantsData = $variants->map(fn ($v) => [
            'id' => $v->id,
            'spec_ids' => $v->specifications->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
            'price' => (int) $v->price,
            'discount_price' => (int) $v->discounted_price,
            'final_price' => (int) $v->final_price,
            'stock' => (int) $v->stock,
            'percent' => $v->percent,
        ])->values();

        // ارزان‌ترین واریانت موجود
        $minVariant = $variants->where('stock', '>', 0)->where('final_price', '>', 0)->sortBy('final_price')->first();

        return compact('mainImage', 'specGroups', 'textValues', 'mainSpecs', 'variantsData', 'minVariant');
    }
}
