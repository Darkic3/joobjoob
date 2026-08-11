<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

/**
 * سیدر محصولات تستی — حدود ۱۰ محصول متنوع برای تست صفحات سایت و پنل ادمین.
 *
 * نکته‌ها:
 * - مستقل و idempotent است: اگر دسته‌بندی/برند لازم وجود نداشت خودش می‌سازد و
 *   با updateOrCreate می‌توان چند بار اجرایش کرد بدون تکرار داده.
 * - تصویر هر محصول یک SVG محلی (بدون نیاز به اینترنت) در
 *   public/{UPLOAD_PATH}/products می‌سازد تا سایت و پنل تصویر واقعی نشان بدهند.
 * - اجرا:  php artisan db:seed --class=TestProductSeeder
 */
class TestProductSeeder extends Seeder
{
    /** جفت‌رنگ گرادینتی برای هر محصول */
    private const PALETTE = [
        ['#6366f1', '#8b5cf6'],
        ['#0ea5e9', '#6366f1'],
        ['#f59e0b', '#ef4444'],
        ['#10b981', '#0ea5e9'],
        ['#8b5cf6', '#ec4899'],
        ['#f97316', '#f43f5e'],
        ['#475569', '#1e293b'],
        ['#06b6d4', '#3b82f6'],
        ['#ef4444', '#f97316'],
        ['#84cc16', '#10b981'],
    ];

    public function run(): void
    {
        // ---------- تضمین وجود دسته‌بندی‌ها (idempotent) ----------
        $clothing = ProductCategory::updateOrCreate(
            ['slug' => 'clothing'],
            ['name' => ['fa' => 'پوشاک', 'en' => 'Clothing'], 'sort_order' => 1, 'is_active' => true]
        );
        $socks = ProductCategory::updateOrCreate(
            ['slug' => 'socks'],
            ['parent_id' => $clothing->id, 'name' => ['fa' => 'جوراب', 'en' => 'Socks'], 'sort_order' => 1, 'is_active' => true]
        );
        $tshirts = ProductCategory::updateOrCreate(
            ['slug' => 'tshirts-polos'],
            ['parent_id' => $clothing->id, 'name' => ['fa' => 'تی‌شرت و پولوشرت', 'en' => 'T-Shirts & Polos'], 'sort_order' => 2, 'is_active' => true]
        );
        $shoes = ProductCategory::updateOrCreate(
            ['slug' => 'shoes'],
            ['name' => ['fa' => 'کفش', 'en' => 'Shoes'], 'icon' => 'footsteps-outline', 'sort_order' => 3, 'is_active' => true]
        );
        $accessories = ProductCategory::updateOrCreate(
            ['slug' => 'accessories'],
            ['name' => ['fa' => 'اکسسوری', 'en' => 'Accessories'], 'sort_order' => 4, 'is_active' => true]
        );
        $home = ProductCategory::updateOrCreate(
            ['slug' => 'home-kitchen'],
            ['name' => ['fa' => 'خانه و آشپزخانه', 'en' => 'Home & Kitchen'], 'sort_order' => 5, 'is_active' => true]
        );

        // ---------- تضمین وجود برندها (idempotent) ----------
        $brands = [];
        foreach ([
            ['url' => 'nikstyle', 'fa' => 'نایک‌استایل', 'en' => 'NikStyle'],
            ['url' => 'basicwear', 'fa' => 'بیسیک‌ویر', 'en' => 'BasicWear'],
            ['url' => 'charmduz', 'fa' => 'چرم‌دوز', 'en' => 'CharmDuz'],
        ] as $b) {
            $brands[$b['url']] = Brand::updateOrCreate(
                ['url' => $b['url']],
                ['title' => ['fa' => $b['fa'], 'en' => $b['en']], 'active' => true]
            );
        }

        // ---------- محصولات تستی ----------
        $products = [
            [
                'slug' => 'test-sport-cotton-socks',
                'category' => $socks, 'brand' => $brands['nikstyle'],
                'emoji' => '🧦',
                'name' => ['fa' => 'جوراب اسپرت نخی', 'en' => 'Sport Cotton Socks'],
                'short' => ['fa' => 'نخی، تنفس‌پذیر و مناسب باشگاه', 'en' => 'Cotton, breathable, gym-ready'],
                'desc' => ['fa' => 'محصول تستی برای بررسی بخش‌های سایت — جوراب نخی با دوام و ضد بو.', 'en' => 'Test product — durable, breathable cotton socks.'],
                'sku' => 'TSK-101', 'price' => 220000, 'discount' => 179000, 'stock' => 120, 'weight' => 150,
                'featured' => true, 'best' => true, 'deal_days' => 4,
            ],
            [
                'slug' => 'test-basic-tshirt',
                'category' => $tshirts, 'brand' => $brands['basicwear'],
                'emoji' => '👕',
                'name' => ['fa' => 'تی‌شرت ساده نخی', 'en' => 'Plain Cotton T-Shirt'],
                'short' => ['fa' => 'یقه گرد، مناسب هر فصل', 'en' => 'Crew neck, all-season'],
                'desc' => ['fa' => 'محصول تستی — ناموجود برای تست نمایش «ناموجود» و نوار موجودی.', 'en' => 'Test product — out of stock to test the sold-out state.'],
                'sku' => 'TSK-102', 'price' => 380000, 'discount' => 0, 'stock' => 0, 'weight' => 250,
                'featured' => true, 'best' => true,
            ],
            [
                'slug' => 'test-leather-belt',
                'category' => $accessories, 'brand' => $brands['charmduz'],
                'emoji' => '👖',
                'name' => ['fa' => 'کمربند چرم طبیعی', 'en' => 'Genuine Leather Belt'],
                'short' => ['fa' => 'چرم گاوی با سگک ضدزنگ', 'en' => 'Cowhide with rust-proof buckle'],
                'desc' => ['fa' => 'محصول تستی با تخفیف و تایمر — موجودی کم برای تست بج فوریت.', 'en' => 'Test product with discount & timer — low stock for urgency badge.'],
                'sku' => 'TSK-103', 'price' => 650000, 'discount' => 520000, 'stock' => 8, 'weight' => 300,
                'featured' => true, 'best' => false, 'deal_days' => 3,
            ],
            [
                'slug' => 'test-baseball-cap',
                'category' => $accessories, 'brand' => $brands['nikstyle'],
                'emoji' => '🧢',
                'name' => ['fa' => 'کلاه بیسبال گلدوزی', 'en' => 'Embroidered Baseball Cap'],
                'short' => ['fa' => 'نخی، با سایز قابل تنظیم', 'en' => 'Cotton with adjustable strap'],
                'desc' => ['fa' => 'محصول تستی — تخفیف بدون تایمر برای تست تفاوت بخش‌ها.', 'en' => 'Test product — discount without a timer.'],
                'sku' => 'TSK-104', 'price' => 290000, 'discount' => 240000, 'stock' => 45, 'weight' => 120,
                'featured' => true, 'best' => true,
            ],
            [
                'slug' => 'test-wool-scarf',
                'category' => $accessories, 'brand' => $brands['basicwear'],
                'emoji' => '🧣',
                'name' => ['fa' => 'شال گردن پشمی', 'en' => 'Wool Scarf'],
                'short' => ['fa' => 'پشم مرغوب، بافت دستی', 'en' => 'Premium wool, hand-woven'],
                'desc' => ['fa' => 'محصول تستی بدون تخفیف — قیمت تمام برای تست حالت عادی.', 'en' => 'Test product without discount — full price state.'],
                'sku' => 'TSK-105', 'price' => 420000, 'discount' => 0, 'stock' => 30, 'weight' => 200,
                'featured' => true, 'best' => false,
            ],
            [
                'slug' => 'test-canvas-shoulder-bag',
                'category' => $accessories, 'brand' => $brands['charmduz'],
                'emoji' => '👜',
                'name' => ['fa' => 'کیف دوشی بوم', 'en' => 'Canvas Shoulder Bag'],
                'short' => ['fa' => 'بوم ضخیم با جیب داخلی', 'en' => 'Heavy canvas with inner pocket'],
                'desc' => ['fa' => 'محصول تستی — پرفروش با تخفیف و تایمر.', 'en' => 'Test product — best seller with discount & timer.'],
                'sku' => 'TSK-106', 'price' => 780000, 'discount' => 620000, 'stock' => 15, 'weight' => 500,
                'featured' => false, 'best' => true, 'deal_days' => 2,
            ],
            [
                'slug' => 'test-sport-watch',
                'category' => $accessories, 'brand' => $brands['nikstyle'],
                'emoji' => '⌚',
                'name' => ['fa' => 'ساعت مچی اسپرت', 'en' => 'Sport Wrist Watch'],
                'short' => ['fa' => 'ضدآب با بند سیلیکونی', 'en' => 'Waterproof with silicone strap'],
                'desc' => ['fa' => 'محصول تستی گران‌قیمت با تخفیف — ویژه و پرفروش.', 'en' => 'Test product — premium with discount, featured & best seller.'],
                'sku' => 'TSK-107', 'price' => 1850000, 'discount' => 1490000, 'stock' => 5, 'weight' => 180,
                'featured' => true, 'best' => true,
            ],
            [
                'slug' => 'test-sunglasses',
                'category' => $accessories, 'brand' => $brands['basicwear'],
                'emoji' => '🕶',
                'name' => ['fa' => 'عینک آفتابی پولاریزه', 'en' => 'Polarized Sunglasses'],
                'short' => ['fa' => 'محافظ UV با فریم سبک', 'en' => 'UV protection, lightweight frame'],
                'desc' => ['fa' => 'محصول تستی — پرفروش بدون تخفیف.', 'en' => 'Test product — best seller without discount.'],
                'sku' => 'TSK-108', 'price' => 950000, 'discount' => 0, 'stock' => 25, 'weight' => 90,
                'featured' => false, 'best' => true,
            ],
            [
                'slug' => 'test-running-shoes',
                'category' => $shoes, 'brand' => $brands['nikstyle'],
                'emoji' => '👟',
                'name' => ['fa' => 'کتانی رانینگ', 'en' => 'Running Sneakers'],
                'short' => ['fa' => 'کفی فنری با رویه مش', 'en' => 'Springy sole with mesh upper'],
                'desc' => ['fa' => 'محصول تستی — ویژه با تخفیف و تایمر.', 'en' => 'Test product — featured with discount & timer.'],
                'sku' => 'TSK-109', 'price' => 2400000, 'discount' => 1990000, 'stock' => 40, 'weight' => 800,
                'featured' => true, 'best' => false, 'deal_days' => 6,
            ],
            [
                'slug' => 'test-ceramic-mug',
                'category' => $home, 'brand' => $brands['basicwear'],
                'emoji' => '☕',
                'name' => ['fa' => 'لیوان سرامیکی', 'en' => 'Ceramic Mug'],
                'short' => ['fa' => 'سرامیک لعاب‌دار ۳۳۰ میل', 'en' => 'Glazed ceramic, 330ml'],
                'desc' => ['fa' => 'محصول تستی عادی — نه ویژه، نه پرفروش، بدون تخفیف.', 'en' => 'Plain test product — not featured, not best seller, no discount.'],
                'sku' => 'TSK-110', 'price' => 180000, 'discount' => 0, 'stock' => 70, 'weight' => 350,
                'featured' => false, 'best' => false,
            ],
        ];

        foreach ($products as $i => $p) {
            $image = $this->writeSvgPlaceholder($i + 1, $p['slug'], $p['emoji']);

            $data = [
                'product_category_id' => $p['category']->id,
                'brand_id' => $p['brand']?->id,
                'name' => $p['name'],
                'short_description' => $p['short'],
                'description' => $p['desc'],
                'sku' => $p['sku'],
                'main_image' => $image,
                'price' => $p['price'],
                'discount_price' => $p['discount'] ?: null,
                'final_price' => $p['discount'] ?: $p['price'],
                'stock' => $p['stock'],
                'weight' => $p['weight'],
                'is_active' => true,
                'is_featured' => $p['featured'],
                'is_best_seller' => $p['best'],
                'timer_active' => isset($p['deal_days']),
                'start_timer' => isset($p['deal_days']) ? now() : null,
                'end_timer' => isset($p['deal_days']) ? now()->addDays($p['deal_days']) : null,
            ];

            Product::updateOrCreate(['slug' => $p['slug']], $data);
        }

        $this->command?->info('۱۰ محصول تستی (با تصویر SVG) اضافه شدند.');
    }

    /**
     * ساخت و ذخیرهٔ یک SVG جای‌گذار «استودیویی» در پوشهٔ آپلود محصولات:
     * پس‌زمینهٔ رادیال ملایم (روشن از مرکز) + سایهٔ زمینی + ایموجی محصول با سایهٔ معلق —
     * شبیه عکس محصول واقعی روی پس‌زمینه، نه یک مربع صاف رنگی.
     */
    private function writeSvgPlaceholder(int $index, string $slug, string $emoji): string
    {
        $dir = public_path(config('paths.upload', 'uploads').'/products');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        [$from, $to] = self::PALETTE[($index - 1) % count(self::PALETTE)];
        $light = $this->lighten($from, 0.55);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">'
            .'<defs>'
            .'<radialGradient id="bg" cx="50%" cy="38%" r="80%">'
            .'<stop offset="0%" stop-color="#ffffff"/>'
            .'<stop offset="55%" stop-color="'.$light.'"/>'
            .'<stop offset="100%" stop-color="'.$from.'"/>'
            .'</radialGradient>'
            .'<filter id="ds" x="-30%" y="-30%" width="160%" height="160%">'
            .'<feDropShadow dx="0" dy="14" stdDeviation="16" flood-color="rgba(0,0,0,.28)"/>'
            .'</filter>'
            .'</defs>'
            .'<rect width="600" height="600" fill="url(#bg)"/>'
            // سایهٔ زمینی زیر محصول (بیضی نرم)
            .'<ellipse cx="300" cy="478" rx="165" ry="24" fill="rgba(0,0,0,.10)"/>'
            // محصول معلق با سایه
            .'<text x="300" y="410" text-anchor="middle" '
            .'font-family="\'Segoe UI Emoji\', \'Apple Color Emoji\', \'Noto Color Emoji\', sans-serif" '
            .'font-size="220" filter="url(#ds)">'.$emoji.'</text>'
            .'</svg>';

        $filename = $slug.'.svg';
        file_put_contents($dir.'/'.$filename, $svg);

        return $filename;
    }

    /**
     * روشن‌کردن یک رنگ hex به سمت سفید (0 = بدون تغییر، 1 = سفید خالص)
     */
    private function lighten(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = (int) round($r + (255 - $r) * $amount);
        $g = (int) round($g + (255 - $g) * $amount);
        $b = (int) round($b + (255 - $b) * $amount);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
