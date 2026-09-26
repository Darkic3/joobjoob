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

    public function test_home_renders_only_the_first_active_slide_in_minimal_hero(): void
    {
        HeroSlide::create([
            'title' => ['fa' => 'اسلاید اول', 'en' => 'First'],
            'is_active' => true,
            'sort_order' => 1,
        ]);
        HeroSlide::create([
            'title' => ['fa' => 'اسلاید دوم', 'en' => 'Second'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('hero-minimal', $html);
        $this->assertStringContainsString('اسلاید اول', $html);
        // اسلاید دوم هرگز در نسخهٔ مینیمال نمایش داده نمی‌شود
        $this->assertStringNotContainsString('اسلاید دوم', $html);
    }

    public function test_first_slide_wins_even_when_an_earlier_inactive_one_exists(): void
    {
        HeroSlide::create([
            'title' => ['fa' => 'خاموش', 'en' => 'Off'],
            'is_active' => false,
            'sort_order' => 1,
        ]);
        HeroSlide::create([
            'title' => ['fa' => 'روشن', 'en' => 'On'],
            'is_active' => true,
            'sort_order' => 5,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('روشن', $html);
        $this->assertStringNotContainsString('خاموش', $html);
    }

    public function test_default_hero_renders_when_no_active_slide(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('hero-minimal', $html);
        $this->assertStringContainsString(__('messages.hero_default_title'), $html);
    }
}
