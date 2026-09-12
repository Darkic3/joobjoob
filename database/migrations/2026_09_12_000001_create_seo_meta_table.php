<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول واحد متای SEO برای همهٔ موجودیت‌ها + صفحات بدون مدل، به‌ازای هر زبان.
 *
 * دو نوع هدف (دقیقاً یکی باید پر باشد — در SeoMetaRequest و HasSeoMeta::syncSeoMeta اعتبارسنجی می‌شود):
 *   ۱) entity:  seo_metable_type + seo_metable_id  (محصول، دسته‌بندی، مقاله، برند)
 *   ۲) path:    path                                (صفحهٔ اصلی، /products، /page/faq)
 *
 * دلیل انتخاب چندریختی به‌جای ستون‌های JSON روی هر جدول: یک محل ویرایش، یک رزولور،
 * و پوشش صفحات بدون مدل — بدون نیاز به مهاجرت دوباره برای فازهای بعدی (برند لندینگ،
 * Faceted SEO، متای مسیر دلخواه).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();

            // ── هدف متا: entity (morph) ──
            $table->nullableMorphs('seo_metable');

            // ── هدف متا: مسیر بدون مدل ──
            $table->string('path', 500)->nullable();

            // ── زبان (fa/en/ar/tr) ──
            $table->string('locale', 10);

            // ── ایندکس‌پذیری ──
            $table->boolean('is_indexable')->default(true);
            $table->boolean('is_followable')->default(true);

            // ── متای اصلی ──
            $table->string('title', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('focus_keyword', 191)->nullable();
            $table->string('canonical_url', 500)->nullable();

            // ── Open Graph ──
            $table->string('og_title', 191)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('og_type', 32)->nullable();
            $table->string('og_url', 500)->nullable();

            // ── Twitter Card ──
            $table->string('twitter_card', 32)->nullable();
            $table->string('twitter_site', 64)->nullable();
            $table->string('twitter_creator', 64)->nullable();
            $table->string('twitter_title', 191)->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image', 500)->nullable();

            // ── Structured Data ──
            $table->string('schema_type', 64)->nullable();
            $table->json('schema_json')->nullable();

            // ── Sitemap ──
            $table->decimal('sitemap_priority', 2, 1)->nullable();
            $table->string('sitemap_changefreq', 16)->nullable();

            $table->timestamps();

            // یک ردیف برای هر (entity، زبان)
            $table->unique(
                ['seo_metable_type', 'seo_metable_id', 'locale'],
                'seo_meta_metable_locale_unique'
            );

            // یک ردیف برای هر (مسیر، زبان)
            // طول کلید: 500×4 + 10×4 ≈ 2040 بایت — زیر سقف ۳۰۷۲ بایتی InnoDB
            $table->unique(['path', 'locale'], 'seo_meta_path_locale_unique');

            // برای لیست پنل: «کدام صفحات این زبان نوایندکس شده‌اند؟»
            $table->index(['locale', 'is_indexable'], 'seo_meta_locale_indexable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
