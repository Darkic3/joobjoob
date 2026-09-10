<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function enableNewsletter(): void
    {
        Setting::updateOrCreate(
            ['key' => 'newsletter_enabled'],
            ['value' => '1', 'type' => 'boolean', 'group' => 'newsletter', 'label' => 'x']
        );
        app(\App\Services\SettingService::class)->flush();
    }

    // ── Footer subscribe ──

    public function test_footer_subscribe_persists_email_when_enabled(): void
    {
        $this->enableNewsletter();

        $this->postJson(route('newsletter.subscribe'), ['email' => 'user@example.com'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'user@example.com',
            'is_active' => true,
        ]);
    }

    public function test_footer_subscribe_rejected_when_disabled(): void
    {
        $this->postJson(route('newsletter.subscribe'), ['email' => 'user@example.com'])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'user@example.com']);
    }

    public function test_footer_subscribe_is_idempotent(): void
    {
        $this->enableNewsletter();

        $this->postJson(route('newsletter.subscribe'), ['email' => 'dup@example.com'])->assertOk();
        $this->postJson(route('newsletter.subscribe'), ['email' => 'dup@example.com'])->assertOk();

        $this->assertEquals(1, NewsletterSubscriber::where('email', 'dup@example.com')->count());
    }

    // ── Account email ──

    public function test_user_can_update_email(): void
    {
        $user = User::factory()->create(['email' => null]);
        $this->actingAs($user);

        $this->put(route('dashboard.email.update'), ['email' => 'new@example.com'])
            ->assertRedirect();

        $this->assertEquals('new@example.com', $user->fresh()->email);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => null]);
        $this->actingAs($user);

        $this->put(route('dashboard.email.update'), ['email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_delete_email_and_unsubscribes(): void
    {
        $this->enableNewsletter();
        $user = User::factory()->create(['email' => 'me@example.com']);
        NewsletterSubscriber::create(['email' => 'me@example.com', 'user_id' => $user->id, 'is_active' => true]);
        $this->actingAs($user);

        $this->delete(route('dashboard.email.destroy'))->assertRedirect();

        $this->assertNull($user->fresh()->email);
        $this->assertFalse(
            NewsletterSubscriber::where('email', 'me@example.com')->first()->is_active
        );
    }

    // ── Account newsletter toggle ──

    public function test_toggle_subscribes_and_unsubscribes(): void
    {
        $this->enableNewsletter();
        $user = User::factory()->create(['email' => 'toggle@example.com']);
        $this->actingAs($user);

        $this->postJson(route('dashboard.newsletter.toggle'))
            ->assertOk()
            ->assertJson(['success' => true, 'subscribed' => true]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'toggle@example.com', 'is_active' => true,
        ]);

        $this->postJson(route('dashboard.newsletter.toggle'))
            ->assertOk()
            ->assertJson(['success' => true, 'subscribed' => false]);

        $this->assertFalse(
            NewsletterSubscriber::where('email', 'toggle@example.com')->first()->is_active
        );
    }

    public function test_toggle_rejected_when_disabled(): void
    {
        $user = User::factory()->create(['email' => 'off@example.com']);
        $this->actingAs($user);

        $this->postJson(route('dashboard.newsletter.toggle'))
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_toggle_requires_email(): void
    {
        $this->enableNewsletter();
        $user = User::factory()->create(['email' => null]);
        $this->actingAs($user);

        $this->postJson(route('dashboard.newsletter.toggle'))
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    // ── Rendering ──

    public function test_footer_hides_newsletter_when_disabled(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post(route('cart.add', $this->makeProduct()->id));

        $html = $this->get(route('cart.index'))->getContent();

        $this->assertStringNotContainsString('site-footer-newsletter-input', $html);
    }

    public function test_footer_shows_newsletter_when_enabled(): void
    {
        $this->enableNewsletter();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post(route('cart.add', $this->makeProduct()->id));

        $html = $this->get(route('cart.index'))->getContent();

        $this->assertStringContainsString('site-footer-newsletter-input', $html);
    }

    public function test_dashboard_shows_email_and_newsletter_cards(): void
    {
        $this->enableNewsletter();
        $user = User::factory()->create(['email' => 'dash@example.com']);
        $this->actingAs($user);

        $html = $this->get(route('dashboard'))->getContent();

        $this->assertStringContainsString('dash@example.com', $html);
        $this->assertStringContainsString('id="newsletterToggle"', $html);
    }

    private function makeProduct(): \App\Models\Product
    {
        $category = \App\Models\ProductCategory::create([
            'name' => ['fa' => 'دسته تست', 'en' => 'Test Cat'],
            'slug' => 'cat-' . uniqid(),
            'is_active' => true,
        ]);

        return \App\Models\Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'slug' => 'p-' . uniqid(),
            'price' => 150000,
            'final_price' => 150000,
            'stock' => 50,
            'is_active' => true,
        ]);
    }
}
