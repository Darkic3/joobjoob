<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['images', 'category'])->where('status', 'published');

        // ۱. فیلتر دسته‌بندی (چون ممکن است چند دسته انتخاب شود)
        if ($request->filled('category')) {
            $categories = is_array($request->category) ? $request->category : [$request->category];
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $categories));
        }

        // ۲. فیلتر قیمت
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // ۳. فیلتر برند
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // ۴. جستجو در نام محصول (پشتیبانی از JSON چندزبانه)
        if ($request->filled('q')) {
            $searchTerm = '%' . $request->q . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name->fa', 'like', $searchTerm)
                    ->orWhere('name->en', 'like', $searchTerm);
            });
        }

        // ۵. مرتب‌سازی
        match ($request->get('sort')) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'best_selling' => $query->orderBy('is_best_seller', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };

        $products = $query->paginate(12)->withQueryString();

        // دریافت دسته‌بندی‌ها به همراه تعداد محصولات (برای نمایش در سایدبار)
        $categories = ProductCategory::where('is_active', true)
            ->withCount('products')
            ->get();

        // دریافت برندهای یکتا و غیر تهی
        $brands = Product::whereNotNull('brand')
            ->distinct()
            ->pluck('brand');

        return view('site.products.index', compact('products', 'categories', 'brands'));
    }

    public function show(Product $product)
    {
        $product->load(['images', 'category', 'reviews.user']);

        // محصولات مرتبط بر اساس دسته‌بندی
        $related = Product::where('product_category_id', $product->product_category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        return view('site.products.show', compact('product', 'related'));
    }
}
