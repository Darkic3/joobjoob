<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Specification;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request): View
    {
        // دریافت دسته‌بندی‌های فعال برای استفاده در فیلترهای صفحه ایندکس
        $categories = ProductCategory::where('is_active', true)->get();
        $brands = Brand::where('active', true)->orderBy('id')->get();

        // دریافت محصولات صفحه‌بندی شده با حفظ فیلترها
        $products = $this->productService->getPaginatedProducts($request->all());
        $products->appends($request->query());

        // چیپ‌های فیلترهای فعال برای نمایش زیر کارت جستجو — منطق از ویو خارج شده
        $activeFilters = $this->activeFilterChips($request, $categories, $brands);

        return view('admin.products.index', compact('products', 'categories', 'brands', 'activeFilters'));
    }

    /**
     * چیپ‌های قابل‌حذف فیلترهای فعال در ایندکس محصولات.
     *
     * @return array<int, array{label: string, url: string}>
     */
    private function activeFilterChips(Request $request, $categories, $brands): array
    {
        $sortLabels = [
            'newest' => __('admin.products.sort_newest'),
            'oldest' => __('admin.products.sort_oldest'),
            'price_asc' => __('admin.products.sort_price_asc'),
            'price_desc' => __('admin.products.sort_price_desc'),
            'name_asc' => __('admin.products.sort_name_asc'),
            'name_desc' => __('admin.products.sort_name_desc'),
        ];
        $availabilityLabels = [
            'in_stock' => __('admin.products.in_stock'),
            'out_of_stock' => __('admin.products.out_of_stock'),
        ];

        $chips = [];
        if ($request->filled('search')) {
            $chips[] = ['label' => __('admin.common.search').': «'.$request->search.'»', 'url' => route('admin.products.index', $request->except(['page', 'search']))];
        }
        if ($request->filled('category')) {
            $catName = $categories->firstWhere('id', (int) $request->category)?->trans('name') ?? __('admin.products.no_category');
            $chips[] = ['label' => __('admin.common.category').': '.$catName, 'url' => route('admin.products.index', $request->except(['page', 'category']))];
        }
        if ($request->filled('brand_id')) {
            $brandName = $brands->firstWhere('id', (int) $request->brand_id)?->trans('title') ?? __('admin.products.no_brand');
            $chips[] = ['label' => __('admin.products.field_brand').': '.$brandName, 'url' => route('admin.products.index', $request->except(['page', 'brand_id']))];
        }
        if ($request->has('status') && $request->status !== '') {
            $chips[] = ['label' => __('admin.common.status').': '.($request->status == '1' ? __('admin.common.active') : __('admin.common.inactive')), 'url' => route('admin.products.index', $request->except(['page', 'status']))];
        }
        if ($request->filled('sort') && $request->sort !== 'newest') {
            $chips[] = ['label' => __('admin.products.sort').': '.($sortLabels[$request->sort] ?? $request->sort), 'url' => route('admin.products.index', $request->except(['page', 'sort']))];
        }
        if ($request->filled('availability')) {
            $chips[] = ['label' => __('admin.products.availability').': '.($availabilityLabels[$request->availability] ?? $request->availability), 'url' => route('admin.products.index', $request->except(['page', 'availability']))];
        }
        if ($request->filled('price_min')) {
            $chips[] = ['label' => __('admin.products.min_price').': '.number_format((int) $request->price_min), 'url' => route('admin.products.index', $request->except(['page', 'price_min']))];
        }
        if ($request->filled('price_max')) {
            $chips[] = ['label' => __('admin.products.max_price').': '.number_format((int) $request->price_max), 'url' => route('admin.products.index', $request->except(['page', 'price_max']))];
        }
        if ($request->filled('featured')) {
            $chips[] = ['label' => __('admin.products.only_featured'), 'url' => route('admin.products.index', $request->except(['page', 'featured']))];
        }
        if ($request->filled('best_seller')) {
            $chips[] = ['label' => __('admin.products.only_best'), 'url' => route('admin.products.index', $request->except(['page', 'best_seller']))];
        }

        return $chips;
    }

    public function create(): View
    {
        $categories = ProductCategory::where('is_active', true)->orderByDesc('id')->get();
        $brands = Brand::where('active', true)->orderBy('id')->get();

        // مشخصه‌های والد (گروه‌ها) به همراه فرزندان — برای انتخابگر واریانت
        $specifications = Specification::whereNull('parent_id')->where('active', true)->with('children')->orderBy('id')->get();
        $textSpecifications = Specification::where('type', 'text')->whereNull('parent_id')->where('active', true)->orderBy('id')->get();

        // همه محصولات برای انتخابگر محصولات مرتبط/مکمل
        $products = Product::where('is_active', true)->orderBy('id')->get(['id', 'name', 'slug']);

        return view('admin.products.create', array_merge(
            compact('categories', 'brands', 'specifications', 'textSpecifications', 'products'),
            $this->formData(null)
        ));
    }

    public function store(ProductRequest $request)
    {
        // تبدیل درخواست به DTO
        $productDTO = ProductDTO::fromRequest($request);

        // ذخیره از طریق سرویس
        $this->productService->store($productDTO);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.products.created'));
    }

    public function edit(Product $product): View
    {
        $product->load([
            'images',
            'brand',
            'productSpecifications.specification',
            'productSpecifications.children.specification',
            'variants.specifications',
            'properties',
            'related',
            'complement',
        ]);

        $categories = ProductCategory::where('is_active', true)->orderByDesc('id')->get();
        $brands = Brand::where('active', true)->orderBy('id')->get();

        $specifications = Specification::whereNull('parent_id')->where('active', true)->with('children')->orderBy('id')->get();
        $textSpecifications = Specification::where('type', 'text')->whereNull('parent_id')->where('active', true)->orderBy('id')->get();

        $products = Product::where('is_active', true)->orderBy('id')->get(['id', 'name', 'slug']);

        return view('admin.products.edit', array_merge(
            compact('product', 'categories', 'brands', 'specifications', 'textSpecifications', 'products'),
            $this->formData($product)
        ));
    }

    /**
     * دادهٔ فرم محصول (مقادیر ذخیره‌شده + مقادیر انتخابی مشخصات/واریانت/مرتبط) —
     * منطق آماده‌سازی از ویو خارج شده و این‌جا ساخته می‌شود.
     *
     * @return array{isEdit: bool, oldVal: \Closure, selectedSpecs: array, textSpecValues: array, existingVariants: array, existingProperties: array, relatedIds: array, complementIds: array}
     */
    private function formData(?Product $product): array
    {
        $isEdit = $product !== null;

        // دسترسی امن به مقدار (دادهٔ مدل یا old) با کلید نقطه‌ای
        $oldVal = function (string $key, mixed $default = '') use ($isEdit, $product): mixed {
            if (old($key) !== null) {
                return old($key);
            }

            if ($isEdit && $product) {
                $value = $product;
                foreach (explode('.', $key) as $k) {
                    if (is_object($value) && isset($value->$k)) {
                        $value = $value->$k;
                    } elseif (is_array($value) && isset($value[$k])) {
                        $value = $value[$k];
                    } else {
                        return $default;
                    }
                }

                return $value;
            }

            return $default;
        };

        $selectedSpecs = [];
        $textSpecValues = [];
        $existingVariants = [];
        $existingProperties = [];
        $relatedIds = [];
        $complementIds = [];

        if ($isEdit) {
            foreach ($product->productSpecifications as $ps) {
                // ردیف والد (گروه) — فرزندان آن را می‌گیریم
                if ($ps->value === null && $ps->parent_id === null) {
                    $selectedSpecs[$ps->specification_id] = $ps->children->pluck('specification_id')->map(fn ($id) => (int) $id)->all();
                }
                // مقادیر نوشتاری
                if ($ps->value !== null) {
                    $textSpecValues[$ps->specification_id] = $ps->value;
                }
            }

            foreach ($product->variants as $v) {
                $existingVariants[] = [
                    'id' => $v->id,
                    'spec_ids' => $v->specifications->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
                    'price' => (int) $v->price,
                    'discount_price' => (int) $v->discounted_price,
                    'stock' => (int) $v->stock,
                    'weight' => (int) $v->weight,
                ];
            }

            foreach ($product->properties as $prop) {
                $existingProperties[] = [
                    'id' => $prop->id,
                    'title' => $prop->trans('title') ?? '',
                    'value' => $prop->value,
                ];
            }

            $relatedIds = $product->related->pluck('id')->map(fn ($id) => (int) $id)->all();
            $complementIds = $product->complement->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return compact('isEdit', 'oldVal', 'selectedSpecs', 'textSpecValues', 'existingVariants', 'existingProperties', 'relatedIds', 'complementIds');
    }

    public function update(ProductRequest $request, Product $product)
    {
        $productDTO = ProductDTO::fromRequest($request);

        $this->productService->update($product, $productDTO);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.products.updated'));
    }

    public function destroy(Product $product)
    {
        $this->productService->destroy($product);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.products.deleted'));
    }
}
