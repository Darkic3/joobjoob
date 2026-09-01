<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_accessing_admin_products(): void
    {
        $response = $this->get('/admin/products');

        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login_when_accessing_admin_settings(): void
    {
        $response = $this->get('/admin/settings');

        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function unauthenticated_user_is_not_redirected_to_403(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.login'));
        $response->assertDontSee('403');
    }

    /** @test */
    public function regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        // Non-admin users should be redirected, not shown the dashboard
        $response->assertStatus(302);
    }

    /** @test */
    public function admin_login_page_is_accessible_without_authentication(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
    }

    /** @test */
    public function admin_login_route_returns_the_correct_view(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('ورود'); // Persian login text
    }
}
