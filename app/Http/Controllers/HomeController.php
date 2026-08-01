<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Services\HeroSlideService;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function __construct(protected  HeroSlideService $heroSlideService)
    {
    }

    public function index()
    {
        $categories = ProductCategory::whereNull('parent_id')->where('is_active', true)->withCount('products')->take(8)->get();
        $deals = Product::with('images')->where('status', 'published')->whereNotNull('deal_ends_at')->where('deal_ends_at', '>', now())->take(4)->get()
            ->map(function ($product) {
                // محاسبه درصد فروش (فرض: اگر sold_count در دیتابیس دارید از آن استفاده کنید)
                // در اینجا یک فرمول تقریبی بر اساس موجودی فعلی و فرض فروش ۵۰ عدد استفاده شده است
                $estimatedSold = 50;
                $total = $estimatedSold + $product->stock;
                $product->sold_percent = $total > 0 ? min(100, round(($estimatedSold / $total) * 100)) : 0;
                return $product;
            });
        $featured = Product::with('images')->where('status', 'published')->where('is_featured', true)->take(4)->get();
        $bestSellers = Product::with('images')->where('status', 'published')->where('is_best_seller', true)->take(4)->get();
        $reviews = Review::with(['user', 'product'])->approved()->latest()->take(3)->get();        $slides = $this->heroSlideService->getSlides();
        return view('site.home.index', compact('categories', 'deals', 'featured', 'bestSellers', 'reviews','slides'));
    }


    public function seed()
    {
        if (!app()->environment('local')) {
            abort(403, 'این عملیات فقط در محیط توسعه مجاز است.');
        }

        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\ProductCategorySeeder::class
        ]);

        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\ProductSeeder::class
        ]);
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\ReviewSeeder::class
        ]);


        return response()->json([
            'message' => 'داده‌های نمونه با موفقیت در دیتابیس ثبت شدند.',
            'output' => Artisan::output()
        ]);

    }
}
