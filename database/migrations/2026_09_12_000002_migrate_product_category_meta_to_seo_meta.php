<?php

use App\Models\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * انتقال متای فعلی دسته‌بندی محصولات به جدول واحد `seo_meta`.
 *
 * چرا لازم است: `product_categories.meta_title/meta_description` امروز در پنل ادمین
 * قابل ویرایش است و در محیط پروداکشن ممکن است مقدار داشته باشد. بدون این انتقال،
 * حذف ستون‌ها در فاز ۶ داده‌ی واقعی را از بین می‌برد.
 *
 * ستون‌های قدیمی در این مهاجرت **دست‌نخورده** می‌مانند (فقط کپی می‌شوند) تا مسیر
 * فعلی خواندن/نوشتن آن‌ها نشکند. حذف ستون‌ها فاز ۶ است.
 *
 * ⚠️ down() عمداً همهٔ ردیف‌های متای دسته‌بندی محصولات را پاک می‌کند: این مهاجرت
 * «متای دسته‌بندی در seo_meta زندگی می‌کند» را اعلام می‌کند و برگشتش همان اعلام را
 * باطل می‌کند. در زمان اجرای این مهاجرت هیچ رابط کاربری برای نوشتن متای دسته‌بندی
 * در seo_meta وجود ندارد (پنل SEO فاز ۵ است)، پس ردیفِ ساخته‌شده توسط ادمین وجود ندارد.
 */
return new class extends Migration
{
    public function up(): void
    {
        $morphClass = (new ProductCategory())->getMorphClass();

        $categories = DB::table('product_categories')
            ->select(['id', 'meta_title', 'meta_description'])
            ->where(function ($query) {
                $query->whereNotNull('meta_title')->orWhereNotNull('meta_description');
            })
            ->get();

        $now = now();
        $rows = [];

        foreach ($categories as $category) {
            $titles = $this->decodeJsonMap($category->meta_title);
            $descriptions = $this->decodeJsonMap($category->meta_description);

            // اجتماع کلیدهای هر دو ستون — ممکن است فقط یکی از آن‌ها مقدار داشته باشد
            foreach (array_unique(array_merge(array_keys($titles), array_keys($descriptions))) as $locale) {
                $title = $this->clean($titles[$locale] ?? null);
                $description = $this->clean($descriptions[$locale] ?? null);

                if ($title === null && $description === null) {
                    continue;
                }

                $rows[] = [
                    'seo_metable_type' => $morphClass,
                    'seo_metable_id' => $category->id,
                    'path' => null,
                    'locale' => $locale,
                    'is_indexable' => true,
                    'is_followable' => true,
                    'title' => $title,
                    'description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // درج دسته‌ای — حداکثر ۱۵۰ ستون در هر دستور نیست، فقط ردیف‌ها در چند دسته
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('seo_meta')->insert($chunk);
        }
    }

    public function down(): void
    {
        DB::table('seo_meta')
            ->where('seo_metable_type', (new ProductCategory())->getMorphClass())
            ->delete();
    }

    /**
     * ستون JSON را به نقشهٔ locale => مقدار تبدیل می‌کند.
     * مقدار غیرآرایه (داده‌ی قدیمی/خراب رشته‌ای) به زبان پیش‌فرض نسبت داده می‌شود
     * تا داده از دست نرود.
     *
     * @return array<string, mixed>
     */
    private function decodeJsonMap(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        if (! is_array($decoded)) {
            return ['fa' => $value];
        }

        return $decoded;
    }

    private function clean(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
};
