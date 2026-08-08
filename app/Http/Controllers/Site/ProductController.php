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

        // ۱. فیلتر دسته‌بندی
        if ($request->filled('category')) {
            $categories = is_array($request->category) ? $request->category : [$request->category];
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $categories));
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
            $searchTerm = '%' . $request->q . '%';
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

        return view('site.products.index', compact('products', 'categories', 'brands'));
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

        return view('site.products.show', compact('product', 'related', 'complement'));
    }
}
