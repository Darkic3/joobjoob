<?php

namespace Tests\Feature;

use App\Models\TrustBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustBadgePlacementTest extends TestCase
{
    use RefreshDatabase;

    private function badge(string $placement, bool $active = true): TrustBadge
    {
        return TrustBadge::create([
            'icon' => '🛡️',
            'title' => ['fa' => 'نشان '.$placement, 'en' => 'Badge '.$placement],
            'description' => null,
            'is_active' => $active,
            'placement' => $placement,
        ]);
    }

    public function test_footer_placement_appears_in_footer_bottom_bar_not_homepage_strip(): void
    {
        $this->badge('footer');

        $html = $this->get('/')->assertOk()->getContent();

        // در نوار پایین فوتر دیده می‌شود
        $this->assertStringContainsString('site-footer-badge', $html);
        $this->assertStringContainsString('نشان footer', $html);
        // اما در نوار اعتماد صفحهٔ اصلی نه
        $this->assertStringNotContainsString('trust-strip', $html);
    }

    public function test_site_placement_appears_in_homepage_strip_not_footer(): void
    {
        $this->badge('site');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('trust-strip', $html);
        $this->assertStringContainsString('نشان site', $html);
        $this->assertStringNotContainsString('site-footer-badge', $html);
    }

    public function test_both_placement_appears_everywhere(): void
    {
        $this->badge('both');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('trust-strip', $html);
        $this->assertStringContainsString('site-footer-badge', $html);
    }

    public function test_inactive_badge_hidden_everywhere(): void
    {
        $this->badge('footer', false);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('site-footer-badge', $html);
        $this->assertStringNotContainsString('trust-strip', $html);
    }
}
