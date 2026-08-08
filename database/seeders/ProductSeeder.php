<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\Property;
use App\Models\Specification;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $socksCategory = ProductCategory::where('slug', 'socks')->first();
        $tshirtCategory = ProductCategory::where('slug', 'tshirts-polos')->first();
        $accessoriesCategory = ProductCategory::where('slug', 'accessories')->first();

        $nik = Brand::where('url', 'nikstyle')->first();
        $basic = Brand::where('url', 'basicwear')->first();
        $charm = Brand::where('url', 'charmduz')->first();

        // ============ مشخصات (از SpecificationSeeder) ============
        $colorGroup = Specification::where('parent_id', null)->where('title->fa', 'رنگ')->first();
        $sizeGroup = Specification::where('parent_id', null)->where('title->fa', 'سایز')->first();
        $materialText = Specification::where('parent_id', null)->where('title->fa', 'جنس')->first();
        $countryText = Specification::where('parent_id', null)->where('title->fa', 'کشور سازنده')->first();

        $colorValues = $colorGroup ? Specification::where('parent_id', $colorGroup->id)->get() : collect();
        $sizeValues = $sizeGroup ? Specification::where('parent_id', $sizeGroup->id)->get() : collect();

        // helper برای پیدا کردن مقدار بر اساس title.fa
        // (firstWhere با کلید JSON مانند title->fa روی Collection کار نمی‌کند)
        $findValue = function ($collection, string $fa) {
            foreach ($collection as $item) {
                if (($item->title['fa'] ?? '') === $fa) return $item;
            }
            return null;
        };

        // ============ محصول ۱: جوراب (واریانت: رنگ × سایز) ============
        $socks = Product::updateOrCreate(
            ['slug' => 'men-patterned-cotton-socks-3pack'],
            [
                'product_category_id' => $socksCategory->id,
                'brand_id' => $nik?->id,
                'name' => ['fa' => 'جوراب نخی مردانه طرح‌دار (بسته ۳ عددی)', 'en' => 'Men Patterned Cotton Socks (3-Pack)'],
                'short_description' => ['fa' => 'نخ پنبه‌ی طبیعی، بدون حساسیت و با دوام بالا', 'en' => 'Natural cotton, hypoallergenic and highly durable'],
                'description' => ['fa' => 'این جوراب‌ها از بهترین نخ پنبه تهیه شده و برای استفاده روزمره و ورزشی بسیار مناسب هستند...', 'en' => 'Made from the finest cotton, perfect for daily and sports use...'],
                'sku' => 'SK-001',
                'price' => 185000,
                'discount_price' => 149000,
                'final_price' => 149000,
                'stock' => 90,
                'weight' => 150,
                'is_active' => true,
                'is_featured' => true,
                'is_best_seller' => true,
                'end_timer' => now()->addDays(5),
                'timer_active' => true,
            ]
        );

        // مشخصات انتخابی: رنگ (مشکی، سفید) و سایز (39-41، 42-44)
        $this->attachSelectSpecs($socks, [
            [$colorGroup, $colorValues->filter(fn($v) => in_array($v->title['fa'] ?? '', ['مشکی', 'سفید']))],
            [$sizeGroup, $sizeValues->filter(fn($v) => in_array($v->title['fa'] ?? '', ['39-41', '42-44']))],
        ]);

        // واریانت‌ها: ۲ رنگ × ۲ سایز
        $black = $findValue($colorValues, 'مشکی');
        $white = $findValue($colorValues, 'سفید');
        $s3941 = $findValue($sizeValues, '39-41');
        $s4244 = $findValue($sizeValues, '42-44');

        $socksVariants = [
            ['specs' => [$black, $s3941], 'price' => 185000, 'discount' => 149000, 'stock' => 40],
            ['specs' => [$black, $s4244], 'price' => 185000, 'discount' => 149000, 'stock' => 30],
            ['specs' => [$white, $s3941], 'price' => 185000, 'discount' => 149000, 'stock' => 20],
            ['specs' => [$white, $s4244], 'price' => 185000, 'discount' => 149000, 'stock' => 0],
        ];
        foreach ($socksVariants as $sv) {
            $variant = ProductVariant::create([
                'product_id' => $socks->id,
                'price' => $sv['price'],
                'discounted_price' => $sv['discount'],
                'final_price' => $sv['discount'],
                'stock' => $sv['stock'],
                'weight' => 150,
            ]);
            $variant->specifications()->sync(collect($sv['specs'])->pluck('id'));
        }

        // تجمیع: قیمت = ارزان‌ترین واریانت موجود، موجودی = جمع
        $socks->update([
            'price' => 185000,
            'discount_price' => 149000,
            'final_price' => 149000,
            'stock' => $socksVariants ? array_sum(array_column($socksVariants, 'stock')) : 0,
        ]);

        // ویژگی آزاد
        Property::create(['product_id' => $socks->id, 'title' => ['fa' => 'نخ', 'en' => 'Yarn'], 'value' => 'پنبه طبیعی', 'sort_order' => 0]);

        // ============ محصول ۲: تی‌شرت (واریانت: رنگ × سایز) ============
        $tshirt = Product::updateOrCreate(
            ['slug' => 'plain-cotton-crew-neck-tshirt'],
            [
                'product_category_id' => $tshirtCategory->id,
                'brand_id' => $basic?->id,
                'name' => ['fa' => 'تی‌شرت یقه گرد نخی ساده', 'en' => 'Plain Cotton Crew Neck T-Shirt'],
                'short_description' => ['fa' => 'پارچه‌ی پنبه‌ای تنفس‌پذیر، مناسب فصل گرما', 'en' => 'Breathable cotton fabric, perfect for summer'],
                'description' => ['fa' => 'تی‌شرتی با برش استاندارد و پارچه‌ی خنک که پس از شستشو تغییر شکل نمی‌دهد.', 'en' => 'Standard cut and cool fabric that does not lose shape after washing.'],
                'sku' => 'TS-002',
                'price' => 450000,
                'discount_price' => 0,
                'final_price' => 450000,
                'stock' => 60,
                'weight' => 250,
                'is_active' => true,
                'is_featured' => false,
                'is_best_seller' => true,
            ]
        );

        $this->attachSelectSpecs($tshirt, [
            [$colorGroup, $colorValues->filter(fn($v) => in_array($v->title['fa'] ?? '', ['سفید', 'مشکی', 'سورمه‌ای']))],
            [$sizeGroup, $sizeValues->filter(fn($v) => in_array($v->title['fa'] ?? '', ['S', 'M', 'L', 'XL']))],
        ]);

        $whiteT = $findValue($colorValues, 'سفید');
        $blackT = $findValue($colorValues, 'مشکی');
        $navyT = $findValue($colorValues, 'سورمه‌ای');
        $sS = $findValue($sizeValues, 'S');
        $sM = $findValue($sizeValues, 'M');
        $sL = $findValue($sizeValues, 'L');
        $sXL = $findValue($sizeValues, 'XL');

        $tshirtVariants = [];
        foreach (['سفید' => $whiteT, 'مشکی' => $blackT, 'سورمه‌ای' => $navyT] as $key => $colorVal) {
            foreach (['S' => $sS, 'M' => $sM, 'L' => $sL, 'XL' => $sXL] as $sizeKey => $sizeVal) {
                $tshirtVariants[] = [
                    'specs' => [$colorVal, $sizeVal],
                    'price' => 450000,
                    'discount' => 0,
                    'stock' => ($key === 'سفید' && in_array($sizeKey, ['S', 'M'])) ? 15 : 10,
                ];
            }
        }
        foreach ($tshirtVariants as $sv) {
            $variant = ProductVariant::create([
                'product_id' => $tshirt->id,
                'price' => $sv['price'],
                'discounted_price' => $sv['discount'],
                'final_price' => $sv['price'],
                'stock' => $sv['stock'],
                'weight' => 250,
            ]);
            $variant->specifications()->sync(collect($sv['specs'])->pluck('id'));
        }

        $tshirt->update(['stock' => array_sum(array_column($tshirtVariants, 'stock'))]);

        Property::create(['product_id' => $tshirt->id, 'title' => ['fa' => 'یقه', 'en' => 'Neck'], 'value' => 'گرد', 'sort_order' => 0]);

        // ============ محصول ۳: کمربند (بدون واریانت — با مشخصه نوشتاری) ============
        $belt = Product::updateOrCreate(
            ['slug' => 'men-genuine-leather-belt'],
            [
                'product_category_id' => $accessoriesCategory->id,
                'brand_id' => $charm?->id,
                'name' => ['fa' => 'کمربند چرم طبیعی مردانه', 'en' => 'Men Genuine Leather Belt'],
                'short_description' => ['fa' => 'چرم طبیعی گاوی با سگک فلزی ضد زنگ', 'en' => 'Genuine cowhide leather with rust-proof metal buckle'],
                'description' => ['fa' => 'این کمربند از چرم درجه یک تبریز تهیه شده و با هر استایل رسمی یا نیمه‌رسمی ست می‌شود.', 'en' => 'Made from premium Tabriz leather, matches any formal or semi-formal style.'],
                'sku' => 'BLT-003',
                'price' => 890000,
                'discount_price' => 750000,
                'final_price' => 750000,
                'stock' => 40,
                'weight' => 300,
                'is_active' => true,
                'is_featured' => true,
                'is_best_seller' => false,
                'end_timer' => now()->addDays(3),
                'timer_active' => true,
            ]
        );

        // مشخصه نوشتاری: جنس = چرم طبیعی + کشور = ایران
        if ($materialText) {
            ProductSpecification::create([
                'product_id' => $belt->id,
                'specification_id' => $materialText->id,
                'value' => 'چرم طبیعی',
                'parent_id' => null,
            ]);
        }
        if ($countryText) {
            ProductSpecification::create([
                'product_id' => $belt->id,
                'specification_id' => $countryText->id,
                'value' => 'ایران',
                'parent_id' => null,
            ]);
        }

        Property::create(['product_id' => $belt->id, 'title' => ['fa' => 'سگک', 'en' => 'Buckle'], 'value' => 'فلز ضد زنگ', 'sort_order' => 0]);

        // ============ ارتباطات: کمربند مکمل تی‌شرت ============
        $tshirt->complement()->syncWithoutDetaching([$belt->id]);
        $tshirt->related()->syncWithoutDetaching([$socks->id]);

        $this->command?->info('محصولات نمونه با تنوع و مشخصات ساخته شدند.');
    }

    /**
     * اتصال مشخصات انتخابی (گروه + مقادیر) به محصول
     * @param array<int, array{0: ?Specification, 1: \Illuminate\Support\Collection}> $groups
     */
    private function attachSelectSpecs(Product $product, array $groups): void
    {
        foreach ($groups as [$group, $values]) {
            if (!$group || $values->isEmpty()) {
                continue;
            }

            // ردیف والد (گروه)
            $parentRow = ProductSpecification::create([
                'product_id' => $product->id,
                'specification_id' => $group->id,
                'value' => null,
                'parent_id' => null,
            ]);

            // ردیف فرزند برای هر مقدار
            foreach ($values as $value) {
                ProductSpecification::create([
                    'product_id' => $product->id,
                    'specification_id' => $value->id,
                    'value' => null,
                    'parent_id' => $parentRow->id,
                ]);
            }
        }
    }
}
