<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\HeroSlide;
use App\Models\TrustBadge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderActionsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    private function editorWithoutRole(): User
    {
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->detach();

        return $user;
    }

    public function test_hero_slides_reorder_updates_sort_order(): void
    {
        $a = HeroSlide::create(['title' => ['fa' => 'A'], 'is_active' => true, 'sort_order' => 1]);
        $b = HeroSlide::create(['title' => ['fa' => 'B'], 'is_active' => true, 'sort_order' => 2]);
        $c = HeroSlide::create(['title' => ['fa' => 'C'], 'is_active' => true, 'sort_order' => 3]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.hero-slides.reorder'), ['ids' => [$c->id, $a->id, $b->id]])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(1, (int) $c->fresh()->sort_order);
        $this->assertSame(2, (int) $a->fresh()->sort_order);
        $this->assertSame(3, (int) $b->fresh()->sort_order);
    }

    public function test_super_admin_can_store_hero_slide_with_custom_url(): void
    {
        // بازتولید باگ مرج: HeroSlideService::prepareData به $dto->hero_type دسترسی داشت
        $this->actingAs($this->superAdmin())
            ->post(route('admin.hero-slides.store'), [
                'title' => ['fa' => 'اسلاید تست'],
                'button_active' => '1',
                'btn_text' => ['fa' => 'مشاهده'],
                'link_type' => 'custom',
                'link_value' => 'https://example.com',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.hero-slides.index'));

        $slide = HeroSlide::latest('id')->first();
        $this->assertNotNull($slide);
        $this->assertSame('url', $slide->linkable_type);
        $this->assertSame('https://example.com', $slide->linkable_id);
        $this->assertSame(1, (int) $slide->sort_order); // جدید → انتهای لیست
    }

    public function test_hero_slide_stores_trust_items_normalized(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.hero-slides.store'), [
                'title' => ['fa' => 'اسلاید با آیتم‌های اعتماد'],
                'trusts' => [
                    ['icon' => '✓', 'fa' => '+۱۲,۰۰۰ مشتری راضی', 'en' => '+12,000 happy customers'],
                    ['icon' => '🛡', 'fa' => 'تضمین اصالت کالا', 'en' => 'Authenticity Guarantee'],
                    ['fa' => '', 'en' => ''], // آیتم خالی → نادیده گرفته شود
                    ['fa' => 'پشتیبانی ۲۴/۷', 'en' => '24/7 Support'], // بدون آیکون → پیش‌فرض ✓
                    ['icon' => '🚚', 'fa' => 'ارسال سریع', 'en' => 'Fast Shipping'],
                ],
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.hero-slides.index'));

        $slide = HeroSlide::latest('id')->first();
        $trusts = $slide->trusts;

        $this->assertIsArray($trusts);
        $this->assertCount(4, $trusts); // ۵ ورودی − آیتم خالی حذف شده
        $this->assertSame('+۱۲,۰۰۰ مشتری راضی', $trusts[0]['text']['fa']);
        $this->assertSame('+12,000 happy customers', $trusts[0]['text']['en']);
        $this->assertSame('🛡', $trusts[1]['icon']);
        $this->assertSame('✓', $trusts[2]['icon']); // fallback آیکون
        $this->assertSame('ارسال سریع', $trusts[3]['text']['fa']);
        $this->assertSame('Fast Shipping', $trusts[3]['text']['en']);

        // رندر سایت: آیتم‌های ترجمه‌شده
        $this->assertSame('+۱۲,۰۰۰ مشتری راضی', $slide->trustItems()[0]['text']);
        $this->assertSame('✓', $slide->trustItems()[2]['icon']);

        // اسلاید بدون آیتم اعتماد → چیزی نمایش داده نمی‌شود (بدون fallback هاردکد)
        $legacy = HeroSlide::create(['title' => ['fa' => 'قدیمی']]);
        $this->assertSame([], $legacy->trustItems());
    }

    public function test_hero_slide_rejects_more_than_five_trust_items(): void
    {
        $items = [];
        for ($i = 1; $i <= 6; $i++) {
            $items[] = ['icon' => '✓', 'fa' => "آیتم {$i}", 'en' => "Item {$i}"];
        }

        $this->actingAs($this->superAdmin())
            ->post(route('admin.hero-slides.store'), [
                'title' => ['fa' => 'اسلاید بیش از حد'],
                'trusts' => $items,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('trusts');

        $this->assertDatabaseCount('hero_slides', 0);
    }

    public function test_trust_badges_reorder_updates_sort_order(): void
    {
        $a = TrustBadge::create(['icon' => 'a', 'title' => 'A', 'sort_order' => 1, 'is_active' => true]);
        $b = TrustBadge::create(['icon' => 'b', 'title' => 'B', 'sort_order' => 2, 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.trust-badges.reorder'), ['ids' => [$b->id, $a->id]])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(1, (int) $b->fresh()->sort_order);
        $this->assertSame(2, (int) $a->fresh()->sort_order);
    }

    public function test_brands_reorder_updates_sort_order(): void
    {
        $a = Brand::create(['title' => ['fa' => 'A'], 'url' => 'a', 'active' => true, 'sort_order' => 1]);
        $b = Brand::create(['title' => ['fa' => 'B'], 'url' => 'b', 'active' => true, 'sort_order' => 2]);

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.brands.reorder'), ['ids' => [$b->id, $a->id]])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(1, (int) $b->fresh()->sort_order);
        $this->assertSame(2, (int) $a->fresh()->sort_order);
    }

    public function test_reorder_rejects_partial_ids(): void
    {
        $a = HeroSlide::create(['hero_type' => 'informational', 'title' => ['fa' => 'A'], 'is_active' => true, 'sort_order' => 1]);
        $b = HeroSlide::create(['hero_type' => 'informational', 'title' => ['fa' => 'B'], 'is_active' => true, 'sort_order' => 2]);
        HeroSlide::create(['hero_type' => 'informational', 'title' => ['fa' => 'C'], 'is_active' => true, 'sort_order' => 3]);

        // فقط بخشی از اسلایدها → 422 (باید همه ارسال شوند)
        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.hero-slides.reorder'), ['ids' => [$a->id, $b->id]])
            ->assertStatus(422);

        // شناسهٔ ناموجود → خطای اعتبارسنجی
        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.hero-slides.reorder'), ['ids' => [$a->id, $b->id, 99999]])
            ->assertStatus(422);
    }

    public function test_reorder_requires_permission_for_all_modules(): void
    {
        $user = $this->editorWithoutRole();

        $this->actingAs($user)->postJson(route('admin.hero-slides.reorder'), ['ids' => [1]])->assertForbidden();
        $this->actingAs($user)->postJson(route('admin.trust-badges.reorder'), ['ids' => [1]])->assertForbidden();
        $this->actingAs($user)->postJson(route('admin.brands.reorder'), ['ids' => [1]])->assertForbidden();
    }

    public function test_new_brand_goes_to_end_of_order(): void
    {
        Brand::create(['title' => ['fa' => 'A'], 'url' => 'a', 'active' => true, 'sort_order' => 1]);
        $max = (int) Brand::max('sort_order');

        $this->actingAs($this->superAdmin())
            ->post(route('admin.brands.store'), [
                'title' => ['fa' => 'X'],
                'url' => 'x',
                'active' => 1,
            ])
            ->assertRedirect(route('admin.brands.index'));

        $this->assertSame($max + 1, (int) Brand::where('url', 'x')->value('sort_order'));
    }

    public function test_trust_badge_store_works_without_sort_order(): void
    {
        // regression: sort_order از فرم حذف شده — استور باید بدون آن کار کند و آخر لیست باشد
        TrustBadge::create(['icon' => 'a', 'title' => 'A', 'sort_order' => 1, 'is_active' => true]);
        $max = (int) TrustBadge::max('sort_order');

        $this->actingAs($this->superAdmin())
            ->post(route('admin.trust-badges.store'), [
                'icon' => '🚚',
                'title' => 'Test Badge',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.trust-badges.index'));

        $badge = TrustBadge::where('title', 'Test Badge')->firstOrFail();
        $this->assertSame($max + 1, (int) $badge->sort_order);
    }

    public function test_hero_slide_store_goes_to_end_of_order(): void
    {
        HeroSlide::create(['hero_type' => 'informational', 'title' => ['fa' => 'A'], 'is_active' => true, 'sort_order' => 1]);
        $max = (int) HeroSlide::max('sort_order');

        $this->actingAs($this->superAdmin())
            ->post(route('admin.hero-slides.store'), [
                'hero_type' => 'informational',
                'title' => ['fa' => 'اسلاید تست'],
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.hero-slides.index'));

        $slide = HeroSlide::where('title->fa', 'اسلاید تست')->firstOrFail();
        $this->assertSame($max + 1, (int) $slide->sort_order);
    }
}
