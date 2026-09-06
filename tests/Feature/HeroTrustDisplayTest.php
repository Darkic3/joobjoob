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

    public function test_hero_shows_at_most_max_trust_items_shown_chips(): void
    {
        HeroSlide::create([
            'hero_type' => 'informational',
            'title' => ['fa' => 'اسلاید تست', 'en' => 'Test slide'],
            'is_active' => true,
            'sort_order' => 1,
            'trusts' => $this->trustPayload(5),
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $shown = HeroSlide::MAX_TRUST_ITEMS_SHOWN;

        // فقط به اندازهٔ سقف نمایش، چیپ در هیرو رندر می‌شود
        $this->assertSame($shown, substr_count($html, 'class="hero-trust-chip"'));
        // خروجی HTML سمت سرور بومی است
        $this->assertStringContainsString('متن اعتماد ۱', $html);
        $this->assertStringNotContainsString('متن اعتماد ۴', $html);
    }

    public function test_hero_hides_trust_band_when_no_trust_items(): void
    {
        HeroSlide::create([
            'hero_type' => 'informational',
            'title' => ['fa' => 'اسلاید تست', 'en' => 'Test slide'],
            'is_active' => true,
            'sort_order' => 1,
            'trusts' => [],
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('class="hero-trust-chip"', $html);
    }
}
