<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroTrustDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    private function trustPayload(int $count): array
    {
        return collect(range(1, $count))->map(fn (int $i) => [
            'icon' => 'check',
            'text' => ['fa' => "متن اعتماد {$i}", 'en' => "trust {$i}"],
        ])->all();
    }

    public function test_hero_never_renders_trust_chips(): void
    {
        // هیرو مینیمال شد: چیپ‌های اعتماد/امتیاز/ویور حذف‌اند — حتی اگر ادمین ذخیره کرده باشد
        HeroSlide::create([
            'hero_type' => 'informational',
            'title' => ['fa' => 'اسلاید تست', 'en' => 'Test slide'],
            'is_active' => true,
            'sort_order' => 1,
            'trusts' => $this->trustPayload(5),
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('class="hero-trust-chip"', $html);
        $this->assertStringContainsString('hero-minimal', $html);
        $this->assertStringContainsString('اسلاید تست', $html);
    }

    public function test_first_active_slide_by_sort_order_is_rendered(): void
    {
        HeroSlide::create([
            'hero_type' => 'informational',
            'title' => ['fa' => 'اسلاید دوم', 'en' => 'Second'],
            'is_active' => true,
            'sort_order' => 2,
        ]);
        HeroSlide::create([
            'hero_type' => 'informational',
            'title' => ['fa' => 'اسلاید اول', 'en' => 'First'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('اسلاید اول', $html);
        $this->assertStringNotContainsString('اسلاید دوم', $html);
    }

    public function test_default_hero_renders_when_no_active_slide(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('hero-minimal', $html);
        $this->assertStringContainsString(__('messages.hero_default_title'), $html);
    }
}
