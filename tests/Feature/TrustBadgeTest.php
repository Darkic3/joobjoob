<?php

namespace Tests\Feature;

use App\Models\TrustBadge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustBadgeTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_trust_badge_stores_translated_title_and_description(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.trust-badges.store'), [
                'icon' => '🚚',
                'title' => ['fa' => 'ارسال سریع', 'en' => 'Fast Shipping'],
                'description' => ['fa' => 'تحویل ۲۴ ساعته', 'en' => '24h delivery'],
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.trust-badges.index'));

        $badge = TrustBadge::latest('id')->first();

        $this->assertSame(['fa' => 'ارسال سریع', 'en' => 'Fast Shipping'], $badge->title);
        $this->assertSame(['fa' => 'تحویل ۲۴ ساعته', 'en' => '24h delivery'], $badge->description);
        $this->assertSame('site', $badge->placement); // پیش‌فرض: نوار صفحهٔ اصلی

        app()->setLocale('en');
        $this->assertSame('Fast Shipping', $badge->trans('title'));
        $this->assertSame('24h delivery', $badge->trans('description'));

        app()->setLocale('fa');
        $this->assertSame('ارسال سریع', $badge->trans('title'));
    }

    public function test_trust_badge_stores_placement(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.trust-badges.store'), [
                'icon' => '🛡️',
                'title' => ['fa' => 'درگاه پرداخت امن', 'en' => 'Secure Payment'],
                'is_active' => '1',
                'placement' => 'footer',
            ])
            ->assertRedirect(route('admin.trust-badges.index'));

        $this->assertSame('footer', TrustBadge::latest('id')->first()->placement);
    }

    public function test_trust_badge_rejects_invalid_placement(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.trust-badges.store'), [
                'icon' => '🛡️',
                'title' => ['fa' => 'نامعتبر'],
                'is_active' => '1',
                'placement' => 'sidebar',
            ])
            ->assertSessionHasErrors('placement');
    }

    public function test_trust_badge_trans_falls_back_to_fa_when_current_locale_empty(): void
    {
        $badge = TrustBadge::create([
            'icon' => '📦',
            'title' => ['fa' => 'ارسال رایگان', 'en' => ''],
            'description' => null,
            'is_active' => true,
        ]);

        app()->setLocale('en');
        $this->assertSame('ارسال رایگان', $badge->trans('title')); // fallback به fa
        $this->assertSame('', $badge->trans('description'));

        app()->setLocale('fa');
        $this->assertSame('ارسال رایگان', $badge->trans('title'));
    }

    public function test_trust_badge_requires_persian_title(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.trust-badges.store'), [
                'icon' => '🚚',
                'title' => ['fa' => '', 'en' => 'Only English'],
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('title.fa');

        $this->assertDatabaseCount('trust_badges', 0);
    }
}
