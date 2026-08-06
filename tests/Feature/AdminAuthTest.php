<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_admin' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_guest_is_redirected_to_login_for_admin_pages(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
        $this->get('/admin/products')->assertRedirect(route('admin.login'));
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('ورود به پنل');
    }

    public function test_normal_user_cannot_login_to_admin_panel(): void
    {
        $this->makeUser(['email' => 'user@test.dev', 'password' => 'password123']);

        $this->post('/admin/login', [
            'email' => 'user@test.dev',
            'password' => 'password123',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->makeUser(['email' => 'inactive@test.dev', 'password' => 'password123', 'is_active' => false]);

        $this->post('/admin/login', [
            'email' => 'inactive@test.dev',
            'password' => 'password123',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->makeUser(['email' => 'admin@test.dev', 'password' => 'password123', 'is_admin' => true]);

        $this->post('/admin/login', [
            'email' => 'admin@test.dev',
            'password' => 'wrong-password',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_login_and_access_panel(): void
    {
        $this->makeUser(['email' => 'owner@test.dev', 'password' => 'password123', 'is_admin' => true]);

        $this->post('/admin/login', [
            'email' => 'owner@test.dev',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard.index'));

        $this->assertAuthenticated();
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_user_with_role_can_login_and_access_only_their_pages(): void
    {
        // نقش «فقط محصولات»
        $permission = Permission::create([
            'name' => 'admin.products.index',
            'group' => 'products',
            'label_key' => 'permissions.access.products.index',
        ]);
        $role = Role::create(['name' => 'products-only', 'is_system' => false]);
        $role->permissions()->attach($permission);

        $user = $this->makeUser(['email' => 'editor@test.dev', 'password' => 'password123']);
        $user->roles()->attach($role);

        $this->post('/admin/login', [
            'email' => 'editor@test.dev',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard.index'));

        $this->get('/admin/products')->assertOk();
        $this->get('/admin/blogs')->assertForbidden();
    }

    public function test_login_attempt_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'x@test.dev', 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => 'x@test.dev', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_logout_invalidates_session(): void
    {
        $this->makeUser(['email' => 'owner@test.dev', 'password' => 'password123', 'is_admin' => true]);

        $this->post('/admin/login', [
            'email' => 'owner@test.dev',
            'password' => 'password123',
        ]);

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }
}
