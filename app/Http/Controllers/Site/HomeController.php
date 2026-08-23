<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Services\HeroSlideService;
use App\Services\PromoBannerService;
use App\Services\TrustBadgeService;
use Database\Seeders\BrandSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ReviewSeeder;
use Database\Seeders\SpecificationSeeder;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function __construct(
        protected HeroSlideService $heroSlideService,
        protected TrustBadgeService $trustBadgeService,
        protected PromoBannerService $promoBannerService,
    ) {}

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
        $this->attachSubtreeProductCounts($categories);        // محصولات پیشنهاد ویژه: تخفیف‌دار با تایمر فعال و در بازه زمانی معتبر
        $deals = Product::with('images')
            ->where('is_active', true)
            ->where('discount_price', '>', 0)
            ->where(function ($q) {
                $q->whereNull('end_timer')->whereNotNull('deal_ends_at')->where('deal_ends_at', '>', now())
                    ->orWhere(function ($t) {
                        $t->whereNotNull('end_timer')->where('end_timer', '>', now());
                    });
            })
            ->take(4)
            ->get()
            ->map(function ($product) {
                // درصد فروش: هیوریستیک کمبود موجودی (تا زمان راه‌اندازی جدول سفارش‌ها)
                // موجودی کمتر → درصد «فروخته شده» بالاتر = اثر کمبود/فوریت
                $product->sold_percent = $product->stock > 0
                    ? min(100, max(15, (int) round(100 * 50 / (50 + $product->stock))))
                    : 100;

                return $product;
            });

        // زمان پایان پیشنهاد: زودترین پایان در میان پیشنهادهای فعال — برای تایمر بخش
        $dealEndsAt = $deals
            ->map(fn ($product) => $product->end_timer ?? $product->deal_ends_at)
            ->filter()
            ->min();
        // با تبدیل این بخش‌ها به اسکرول افقی، تعداد بیشتری لازم است تا ریل قابل اسکرول باشد
        $featured = Product::with('images')->where('is_active', true)->where('is_featured', true)->take(8)->get();
        $bestSellers = Product::with('images')->where('is_active', true)->where('is_best_seller', true)->take(8)->get();
        // نظرات مشتریان برای چرخش (rotator): فقط نظراتی که ادمین نمایش در صفحه اول را فعال کرده
        $reviews = Review::with(['user', 'product'])->approved()->featured()->latest()->take(12)->get();
        $reviewBatches = $reviews->chunk(3);
        // میانگین امتیاز همهٔ نظرات نمایش‌داده‌شده — محاسبه در کنترلر، نه در ویو
        $reviewsAvg = round($reviews->avg('rating') ?? 0, 1);
        // آخرین مقالات برای صفحهٔ اصلی (۳ مقالهٔ فعال و جدید)
        $latestBlogs = Blog::with('category')->active()->latest()->take(3)->get();
        $slides = $this->heroSlideService->getSlides();

        // نشان‌های اعتماد فعال — محدودیت و کش داخل سرویس مدیریت می‌شود
        $trustBadges = $this->trustBadgeService->getActiveForSite();

        // بنرهای میانی فعال — از ۱ تا ۴ بنر، کش و محدودیت داخل سرویس
        $promoBanners = $this->promoBannerService->getActiveForSite();

        return view('site.home.index', compact('categories', 'deals', 'dealEndsAt', 'featured', 'bestSellers', 'reviews', 'reviewBatches', 'reviewsAvg', 'latestBlogs', 'slides', 'trustBadges', 'promoBanners'));
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
