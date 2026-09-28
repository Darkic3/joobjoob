<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use App\Services\LocaleService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * فاز ۱ — لایهٔ ذخیره‌سازی متای SEO.
 *
 * این تست‌ها قرارداد پایه را قفل می‌کنند: یک ردیف برای هر (هدف، زبان)،
 * نرمال‌سازی مسیر، عدم ساخت ردیف خالی، و معنادار بودن پرچم noindex.
 */
class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Test multi-lingual behavior: disable public locale lock
        config(['site-features.public_locale_locked' => false]);

        app(LocaleService::class)->flush();
        $this->seed(LanguageSeeder::class);
    }

    // ==========================================
    // ساختار جدول
    // ==========================================

    public function test_seo_meta_table_exists_with_per_locale_columns(): void
    {
        $this->assertTrue(Schema::hasTable('seo_meta'));

        foreach ([
            'seo_metable_type', 'seo_metable_id', 'path', 'locale',
            'is_indexable', 'is_followable',
            'title', 'description', 'keywords', 'focus_keyword', 'canonical_url',
            'og_title', 'og_description', 'og_image', 'og_type', 'og_url',
            'twitter_card', 'twitter_site', 'twitter_creator',
            'twitter_title', 'twitter_description', 'twitter_image',
            'schema_type', 'schema_json', 'sitemap_priority', 'sitemap_changefreq',
        ] as $column) {
            $this->assertTrue(
                Schema::hasColumn('seo_meta', $column),
                "ستون «{$column}» در جدول seo_meta وجود ندارد."
            );
        }
    }

    // ==========================================
    // ذخیره‌سازی چندزبانه
    // ==========================================

    public function test_entity_can_store_distinct_meta_per_locale(): void
    {
        $product = $this->product();

        $product->syncSeoMeta([
            'fa' => ['title' => 'عنوان فارسی', 'description' => 'توضیح فارسی'],
            'en' => ['title' => 'English title', 'description' => 'English description'],
        ]);

        $this->assertSame('عنوان فارسی', $product->seoMetaFor('fa')->title);
        $this->assertSame('English title', $product->seoMetaFor('en')->title);
        $this->assertSame(2, $product->seoMeta()->count());
    }

    public function test_saving_same_locale_twice_updates_instead_of_duplicating(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => ['title' => 'نسخه اول']]);
        $product->syncSeoMeta(['fa' => ['title' => 'نسخه دوم']]);

        $this->assertSame(1, $product->seoMeta()->count());
        $this->assertSame('نسخه دوم', $product->seoMetaFor('fa')->title);
    }

    public function test_duplicate_entity_locale_pair_is_rejected_at_database_level(): void
    {
        $product = $this->product();

        SeoMeta::create([
            'seo_metable_type' => $product->getMorphClass(),
            'seo_metable_id' => $product->id,
            'locale' => 'fa',
            'title' => 'اول',
        ]);

        $this->expectException(QueryException::class);

        SeoMeta::create([
            'seo_metable_type' => $product->getMorphClass(),
            'seo_metable_id' => $product->id,
            'locale' => 'fa',
            'title' => 'دوم',
        ]);
    }

    public function test_seo_meta_for_returns_null_when_locale_has_no_row(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['title' => 'فقط فارسی']]);

        $this->assertNull($product->seoMetaFor('en'));
        $this->assertNotNull($product->seoMetaFor('fa'));
    }

    public function test_meta_resolves_back_to_its_owner(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['title' => 'برگشت به مالک']]);

        $meta = $product->seoMetaFor('fa');

        $this->assertInstanceOf(Product::class, $meta->seo_metable);
        $this->assertTrue($meta->seo_metable->is($product));
    }

    // ==========================================
    // نرمال‌سازی مسیر
    // ==========================================

    public function test_path_is_normalized_on_save(): void
    {
        $cases = [
            'products' => '/products',
            '/products' => '/products',
            '/products/' => '/products',
            '/products///' => '/products',
            '/' => '/',
            '' => '/',
            '  /blogs/news  ' => '/blogs/news',
            'https://example.com/page/faq' => '/page/faq',
        ];

        foreach ($cases as $input => $expected) {
            $meta = new SeoMeta(['path' => $input, 'locale' => 'fa']);

            $this->assertSame(
                $expected,
                $meta->path,
                "نرمال‌سازی مسیر برای ورودی «{$input}» درست نیست."
            );
        }
    }

    public function test_entity_rows_keep_path_null(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['title' => 'بدون مسیر']]);

        $this->assertNull($product->seoMetaFor('fa')->path);
    }

    public function test_path_based_rows_are_unique_per_locale(): void
    {
        SeoMeta::create(['path' => '/products', 'locale' => 'fa', 'title' => 'محصولات']);
        SeoMeta::create(['path' => '/products', 'locale' => 'en', 'title' => 'Products']);

        $this->assertSame(2, SeoMeta::query()->forPath('products')->count());

        $this->expectException(QueryException::class);

        SeoMeta::create(['path' => '/products', 'locale' => 'fa', 'title' => 'تکراری']);
    }

    // ==========================================
    // قواعد syncSeoMeta
    // ==========================================

    public function test_empty_strings_are_stored_as_null(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => [
            'title' => 'عنوان معتبر',
            'description' => '',
            'keywords' => '   ',
        ]]);

        $meta = $product->seoMetaFor('fa');

        $this->assertSame('عنوان معتبر', $meta->title);
        $this->assertNull($meta->description);
        $this->assertNull($meta->keywords);
    }

    public function test_meta_title_and_description_are_trimmed(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['title' => '  عنوان با فاصله  ']]);

        $this->assertSame('عنوان با فاصله', $product->seoMetaFor('fa')->title);
    }

    public function test_unsupported_locale_codes_are_skipped(): void
    {
        $product = $this->product();

        $product->syncSeoMeta([
            'fa' => ['title' => 'فارسی'],
            'de' => ['title' => 'Deutsch'],   // در کاتالوگ/دیتابیس نیست
            'xx' => ['title' => 'Unknown'],
        ]);

        $this->assertSame(1, $product->seoMeta()->count());
        $this->assertNotNull($product->seoMetaFor('fa'));
        $this->assertNull($product->seoMetaFor('de'));
    }

    public function test_sync_does_not_create_a_redundant_empty_row(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => ['title' => '', 'description' => '']]);

        $this->assertSame(0, $product->seoMeta()->count());
    }

    public function test_sync_deletes_a_row_that_became_redundant(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => ['title' => 'مقدار اولیه']]);
        $this->assertSame(1, $product->seoMeta()->count());

        // همهٔ مقادیر پاک شدند و پرچم‌ها پیش‌فرض‌اند ⇒ ردیف باید حذف شود
        $product->syncSeoMeta(['fa' => ['title' => '']]);

        $this->assertSame(0, $product->seoMeta()->count());
    }

    public function test_sync_keeps_a_noindex_row_even_without_content(): void
    {
        $product = $this->product();

        // noindex یک تصمیم آگاهانه است، حتی بدون هیچ متن متا
        $product->syncSeoMeta(['fa' => ['title' => '', 'is_indexable' => false]]);

        $meta = $product->seoMetaFor('fa');

        $this->assertNotNull($meta);
        $this->assertFalse($meta->is_indexable);
    }

    public function test_missing_indexability_flag_defaults_to_indexable(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['title' => 'بدون پرچم']]);

        $meta = $product->seoMetaFor('fa');

        $this->assertTrue($meta->is_indexable);
        $this->assertTrue($meta->is_followable);
    }

    public function test_indexability_flags_accept_form_style_values(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => ['title' => 'صفحه', 'is_indexable' => '0', 'is_followable' => '0']]);

        $meta = $product->seoMetaFor('fa');

        $this->assertFalse($meta->is_indexable);
        $this->assertFalse($meta->is_followable);
        $this->assertSame('noindex, nofollow', $meta->robots());
    }

    public function test_non_meta_keys_in_input_are_ignored(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => [
            'title' => 'عنوان',
            'id' => 999,
            'seo_metable_id' => 12345,
            'locale' => 'en',       // نباید زبان ردیف را عوض کند
            'not_a_column' => 'x',
        ]]);

        $meta = $product->seoMetaFor('fa');

        $this->assertSame('عنوان', $meta->title);
        $this->assertSame('fa', $meta->locale);
        $this->assertSame($product->id, $meta->seo_metable_id);
    }

    // ==========================================
    // پرچم‌ها و اسکوپ‌ها
    // ==========================================

    public function test_robots_combines_both_flags(): void
    {
        $cases = [
            [true, true, 'index, follow'],
            [true, false, 'index, nofollow'],
            [false, true, 'noindex, follow'],
            [false, false, 'noindex, nofollow'],
        ];

        foreach ($cases as [$indexable, $followable, $expected]) {
            $meta = new SeoMeta(['is_indexable' => $indexable, 'is_followable' => $followable]);

            $this->assertSame($expected, $meta->robots());
        }
    }

    public function test_indexable_scope_excludes_noindex_rows(): void
    {
        SeoMeta::create(['path' => '/a', 'locale' => 'fa', 'title' => 'A']);
        SeoMeta::create(['path' => '/b', 'locale' => 'fa', 'title' => 'B', 'is_indexable' => false]);

        $this->assertSame(2, SeoMeta::query()->forLocale('fa')->count());
        $this->assertSame(1, SeoMeta::query()->forLocale('fa')->indexable()->count());
    }

    public function test_setting_other_locale_does_not_touch_existing_rows(): void
    {
        $product = $this->product();

        $product->syncSeoMeta(['fa' => ['title' => 'فارسی']]);
        $product->syncSeoMeta(['en' => ['title' => 'English']]);

        $this->assertSame(2, $product->seoMeta()->count());
        $this->assertSame('فارسی', $product->seoMetaFor('fa')->title);
    }

    // ==========================================
    // همهٔ موجودیت‌ها
    // ==========================================

    public function test_every_seo_entity_supports_per_locale_meta(): void
    {
        foreach ($this->seoEntities() as $label => $entity) {
            $entity->syncSeoMeta([
                'fa' => ['title' => "عنوان {$label}"],
                'en' => ['title' => "Title {$label}"],
            ]);

            $this->assertSame(
                "عنوان {$label}",
                $entity->seoMetaFor('fa')?->title,
                "ذخیرهٔ متای «{$label}» درست کار نکرد."
            );

            $this->assertSame(
                "Title {$label}",
                $entity->seoMetaFor('en')?->title,
                "ذخیرهٔ متای «{$label}» برای انگلیسی درست کار نکرد."
            );

            $this->assertSame(2, $entity->seoMeta()->count());
        }
    }

    public function test_target_label_prefers_path_then_morph_target(): void
    {
        $product = $this->product();
        $product->syncSeoMeta(['fa' => ['title' => 'x']]);

        $entityMeta = $product->seoMetaFor('fa');
        $this->assertStringContainsString(Product::class, $entityMeta->targetLabel());
        $this->assertStringContainsString((string) $product->id, $entityMeta->targetLabel());

        $pathMeta = SeoMeta::create(['path' => '/products', 'locale' => 'fa'])->fresh();
        $this->assertSame('/products', $pathMeta->targetLabel());
    }

    // ==========================================
    // ابزارها
    // ==========================================

    /** @return array<string, Model> */
    private function seoEntities(): array
    {
        return [
            'product' => $this->product(),
            'productCategory' => ProductCategory::create([
                'name' => ['fa' => 'دسته', 'en' => 'Category'],
                'slug' => 'seo-cat-'.uniqid(),
            ]),
            'blog' => Blog::create([
                'title' => 'مقاله',
                'slug' => 'seo-blog-'.uniqid(),
                'content' => 'محتوا',
            ]),
            'blogCategory' => BlogCategory::create([
                'title' => 'دسته مقاله',
                'slug' => 'seo-blog-cat-'.uniqid(),
            ]),
            'brand' => Brand::create([
                'title' => ['fa' => 'برند', 'en' => 'Brand'],
                'url' => 'seo-brand-'.uniqid(),
                'active' => true,
            ]),
        ];
    }

    private function product(): Product
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دستهٔ محصول', 'en' => 'Product category'],
            'slug' => 'seo-prod-cat-'.uniqid(),
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test product'],
            'slug' => 'seo-product-'.uniqid(),
            'price' => 1000,
            'is_active' => true,
        ]);
    }
}
