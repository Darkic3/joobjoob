<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Services\HeroSlideService;
use Database\Seeders\BrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ReviewSeeder;
use Database\Seeders\SpecificationSeeder;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function __construct(protected HeroSlideService $heroSlideService) {}

    public function index()
    {
        // دسته‌بندی‌های اصلی (بدون والد) — ترتیب تعیین‌شده توسط ادمین (sort_order) رعایت می‌شود
        $categories = ProductCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->take(8)
            ->get();

        // تعداد محصولات هر دسته‌بندی با احتساب کل زیرشاخه‌ها (بدون N+1)
        $this->attachSubtreeProductCounts($categories);
        // محصولات پیشنهاد ویژه: تخفیف‌دار با تایمر فعال و در بازه زمانی معتبر
        $deals = Product::with('images')
            ->where('is_active', true)
            ->where('discount_price', '>', 0)
            ->where(function ($q) {
                $q->whereNull('end_timer')->whereNotNull('deal_ends_at')->where('deal_ends_at', '>', now())
                    ->orWhere(function ($t) {
                        $t->whereNotNull('end_timer')->where('end_timer', '>', now());
                    });
            })
            ->take(4)->get()
            ->map(function ($product) {
                // محاسبه درصد فروش (فرض: اگر sold_count در دیتابیس دارید از آن استفاده کنید)
                // در اینجا یک فرمول تقریبی بر اساس موجودی فعلی و فرض فروش ۵۰ عدد استفاده شده است
                $estimatedSold = 50;
                $total = $estimatedSold + $product->stock;
                $product->sold_percent = $total > 0 ? min(100, round(($estimatedSold / $total) * 100)) : 0;

                return $product;
            });
        $featured = Product::with('images')->where('is_active', true)->where('is_featured', true)->take(4)->get();
        $bestSellers = Product::with('images')->where('is_active', true)->where('is_best_seller', true)->take(4)->get();
        $reviews = Review::with(['user', 'product'])->approved()->latest()->take(3)->get();
        $slides = $this->heroSlideService->getSlides();

        return view('site.home.index', compact('categories', 'deals', 'featured', 'bestSellers', 'reviews', 'slides'));
    }

    /**
     * تعداد محصولات هر دسته‌بندی را با احتساب کل زیرشاخه‌ها روی مجموعه می‌نشیند.
     * فقط ۲ کوئری اضافی: یکی برای کل دسته‌بندی‌های فعال و یکی برای شمارش محصولات.
     */
    private function attachSubtreeProductCounts($roots): void
    {
        $allActive = ProductCategory::query()
            ->where('is_active', true)
            ->get(['id', 'parent_id']);

        $childrenByParent = $allActive->groupBy('parent_id');

        $countsByCategory = Product::query()
            ->selectRaw('product_category_id, count(*) as total')
            ->whereIn('product_category_id', $allActive->pluck('id'))
            ->groupBy('product_category_id')
            ->pluck('total', 'product_category_id');

        $subtreeTotal = function (int $categoryId) use (&$subtreeTotal, $childrenByParent, $countsByCategory): int {
            $total = (int) ($countsByCategory[$categoryId] ?? 0);
            foreach ($childrenByParent->get($categoryId, collect()) as $child) {
                $total += $subtreeTotal((int) $child->id);
            }

            return $total;
        };

        foreach ($roots as $root) {
            $root->products_count = $subtreeTotal((int) $root->id);
        }
    }

    public function seed()
    {
        if (! app()->environment('local')) {
            abort(403, 'این عملیات فقط در محیط توسعه مجاز است.');
        }

        Artisan::call('db:seed', [
            '--class' => ProductCategorySeeder::class,
        ]);
        Artisan::call('db:seed', [
            '--class' => BrandSeeder::class,
        ]);
        Artisan::call('db:seed', [
            '--class' => SpecificationSeeder::class,
        ]);
        Artisan::call('db:seed', [
            '--class' => ProductSeeder::class,
        ]);
        Artisan::call('db:seed', [
            '--class' => ReviewSeeder::class,
        ]);

        return response()->json([
            'message' => 'داده‌های نمونه با موفقیت در دیتابیس ثبت شدند.',
            'output' => Artisan::output(),
        ]);

    }
}
