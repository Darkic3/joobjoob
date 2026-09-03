<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RBACEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function makeLimitedUser(array $permissionNames): User
    {
        $role = Role::create(['name' => 'limited-' . Str::random(5), 'is_system' => false]);

        foreach ($permissionNames as $name) {
            $perm = Permission::firstOrCreate(
                ['name' => $name],
                [
                    'group' => explode('.', $name)[1] ?? 'misc',
                    'label_key' => "permissions.access." . (explode('.', $name)[1] ?? 'misc') . ".index",
                ]
            );
            $role->permissions()->attach($perm->id);
        }

        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->attach($role->id);

        return $user;
    }

    /** @test */
    public function super_admin_bypasses_all_permission_checks(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        // Even with zero roles, super admin can access everything
        $this->actingAs($admin)->get('/admin/audit-logs')->assertOk();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get('/admin/banks')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }

    /** @test */
    public function user_with_only_audit_permission_cannot_access_products(): void
    {
        $user = $this->makeLimitedUser(['admin.audit-logs.index', 'admin.audit-logs.show']);

        // Can access audit logs
        $this->actingAs($user)->get('/admin/audit-logs')->assertOk();

        // Cannot access products
        $this->actingAs($user)->get('/admin/products')->assertForbidden();

        // Cannot access orders
        $this->actingAs($user)->get('/admin/orders')->assertForbidden();

        // Cannot access settings
        $this->actingAs($user)->get('/admin/settings')->assertForbidden();

        // Cannot access banks
        $this->actingAs($user)->get('/admin/banks')->assertForbidden();

        // Cannot access shipping methods
        $this->actingAs($user)->get('/admin/shipping-methods')->assertForbidden();

        // Cannot access roles
        $this->actingAs($user)->get('/admin/roles')->assertForbidden();

        // Cannot access users
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    /** @test */
    public function user_with_only_product_permission_cannot_access_audit_logs(): void
    {
        $user = $this->makeLimitedUser(['admin.products.index']);

        // Can access products
        $this->actingAs($user)->get('/admin/products')->assertOk();

        // Cannot access audit logs
        $this->actingAs($user)->get('/admin/audit-logs')->assertForbidden();
    }

    /** @test */
    public function user_without_any_roles_is_redirected_home(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);

        // No roles → redirected to home (not login, not 403)
        $this->actingAs($user)->get('/admin/products')->assertRedirect(route('home'));
    }

    /** @test */
    public function dashboard_is_accessible_to_all_admin_users(): void
    {
        $user = $this->makeLimitedUser(['admin.audit-logs.index']);

        // Dashboard is in the "free" list — accessible to anyone with a role
        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
    }
}
