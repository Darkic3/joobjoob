<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Services\RecentlyViewedService;
use App\Services\TrustBadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function __construct(
        private readonly RecentlyViewedService $recentlyViewed,
        private readonly TrustBadgeService $trustBadgeService,
    ) {}

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

    /**
     * مودال پیش‌نمایش سریع محصول — فقط پارشال رندر می‌کند (داخل مودال سراسری قرار می‌گیرد).
     */
    public function quickView(string $product)
    {
        $product = Product::where('slug', $product)->firstOrFail();

        $product->load(['images', 'category', 'brand', 'reviews']);

        return view('site.products.partials.quick-view', compact('product'));
    }

    /**
     * صفحهٔ یک دسته‌بندی محصول — محصولات خود دسته + تمام زیرشاخه‌ها.
     */
    public function category(string $category)
    {
        $category = ProductCategory::where('slug', $category)->firstOrFail();

        abort_unless($category->is_active, 404);

        $category->load('parent');

        $products = Product::with(['images', 'category', 'brand'])
            ->where('is_active', true)
            ->whereIn('product_category_id', $this->categoryIdsWithDescendants([$category->slug]))
            ->paginate(12);

        return view('site.product-categories.show', compact('category', 'products'));
    }

    public function show(string $product)
    {
        $product = Product::where('slug', $product)->firstOrFail();

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

        // ثبت در اخیراً مشاهده‌شده
        $this->recentlyViewed->add($product);

        // ساخت درخت نظرات (نظرات اصلی + پاسخ‌های تو در تو)
        $allReviews = $product->reviews()->with('user')->approved()->oldest()->get();
        $reviewTree = $this->buildReviewTree($allReviews);

        // داده‌های نمایشی صفحهٔ محصول (مشخصات/واریانت‌ها/تصویر اصلی) — از ویو خارج شده
        $viewData = $this->prepareShowData($product);

        // نشان‌های اعتماد فعال برای نمایش نزدیک دکمه خرید
        $trustBadges = $this->trustBadgeService->getActiveForSite(4);

        // تایمر شمارش معکوس تخفیف
        $dealEndsAt = null;
        if ($product->timer_active && $product->end_timer && $product->end_timer->isFuture()) {
            $dealEndsAt = $product->end_timer;
        } elseif ($product->deal_ends_at && $product->deal_ends_at->isFuture()) {
            $dealEndsAt = $product->deal_ends_at;
        }

        return view('site.products.show', array_merge(compact(
            'product', 'related', 'complement', 'reviewTree', 'trustBadges', 'dealEndsAt'
        ), $viewData));
    }

    /**
     * ساخت درخت نظرات — نظرات اصلی + پاسخ‌های تو در تو.
     */
    private function buildReviewTree($allReviews): array
    {
        $map = [];
        $tree = [];

        foreach ($allReviews as $review) {
            $review->replies = collect();
            $map[$review->id] = $review;
        }

        foreach ($allReviews as $review) {
            if ($review->parent_id && isset($map[$review->parent_id])) {
                $map[$review->parent_id]->replies->push($review);
            } else {
                $tree[] = $review;
            }
        }

        return $tree;
    }

    /**
     * داده‌های نمایشی صفحهٔ جزئیات محصول را آماده می‌کند:
     * تصویر اصلی، گروه‌بندی مشخصات (والد/فرزند + مقادیر نوشتاری)،
     * ساختار واریانت برای انتخابگر و ارزان‌ترین واریانت موجود.
     *
     * @return array{mainImage: string, specGroups: array, textValues: array, mainSpecs: array, variantsData: Collection, minVariant: mixed}
     */
    private function prepareShowData(Product $product): array
    {
        $mainImage = $product->main_image_url ?? asset('assets/admin/images/image-not-found.png');

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

    /**
     * ثبت نظر جدید برای محصول.
     */
    public function storeReview(Request $request, string $product)
    {
        $product = Product::where('slug', $product)->firstOrFail();

        $isGuest = !auth()->check();

        $rules = [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
        ];

        $messages = [
            'rating.required'  => 'لطفاً امتیاز را انتخاب کنید.',
            'rating.min'       => 'امتیاز باید حداقل ۱ باشد.',
            'rating.max'       => 'امتیاز حداکثر ۵ است.',
            'comment.required' => 'لطفاً متن نظر را بنویسید.',
            'comment.min'      => 'نظر باید حداقل ۳ حرف باشد.',
            'comment.max'      => 'نظر نمی‌تواند بیش از ۱۰۰۰ حرف باشد.',
        ];

        if ($isGuest) {
            $rules['guest_name']  = 'required|string|min:2|max:100';
            $rules['guest_phone'] = 'required|string|min:10|max:20|regex:/^0[0-9]{9,}$/';
            $messages['guest_name.required']  = 'لطفاً نام خود را وارد کنید.';
            $messages['guest_name.min']       = 'نام باید حداقل ۲ حرف باشد.';
            $messages['guest_phone.required'] = 'لطفاً شماره تلفن خود را وارد کنید.';
            $messages['guest_phone.regex']    = 'شماره تلفن معتبر نیست (مثال: 09123456789).';
        }

        $validated = $request->validate($rules, $messages);

        $data = [
            'user_id'     => auth()->id(),
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'],
            'is_approved' => false,
        ];

        if ($isGuest) {
            $data['guest_name']  = $validated['guest_name'];
            $data['guest_phone'] = $validated['guest_phone'];
        }

        $product->reviews()->create($data);

        return redirect()
            ->route('products.show', $product->slug)
            ->withFragment('tab-reviews')
            ->with('success', __('messages.review_submitted'));
    }

    /**
     * ثبت پاسخ به یک نظر.
     */
    public function storeReply(Request $request, string $product, int|string $review)
    {
        $product = Product::where('slug', $product)->firstOrFail();
        $review = Review::findOrFail($review);

        $isGuest = ! auth()->check();

        $rules = [
            'comment' => 'required|string|min:3|max:500',
        ];
        $messages = [
            'comment.required' => 'لطفاً متن پاسخ را بنویسید.',
            'comment.min'      => 'پاسخ باید حداقل ۳ حرف باشد.',
            'comment.max'      => 'پاسخ نمی‌تواند بیش از ۵۰۰ حرف باشد.',
        ];

        if ($isGuest) {
            $rules['guest_name']  = 'required|string|min:2|max:100';
            $rules['guest_phone'] = 'required|string|min:10|max:20|regex:/^0[0-9]{9,}$/';
            $messages['guest_name.required']  = 'لطفاً نام خود را وارد کنید.';
            $messages['guest_name.min']       = 'نام باید حداقل ۲ حرف باشد.';
            $messages['guest_phone.required'] = 'لطفاً شماره تلفن خود را وارد کنید.';
            $messages['guest_phone.regex']    = 'شماره تلفن معتبر نیست.';
        }

        $validated = $request->validate($rules, $messages);

        $data = [
            'user_id'     => auth()->id(),
            'parent_id'   => $review->id,
            'rating'      => $review->rating, // پاسخ امتیاز نداره
            'comment'     => $validated['comment'],
            'is_approved' => Review::STATUS_PENDING,
        ];

        if ($isGuest) {
            $data['guest_name']  = $validated['guest_name'];
            $data['guest_phone'] = $validated['guest_phone'];
        }

        $product->reviews()->create($data);

        return redirect()
            ->route('products.show', $product->slug)
            ->withFragment('tab-reviews')
            ->with('success', __('messages.reply_submitted'));
    }
}
