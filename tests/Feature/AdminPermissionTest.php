<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function permission(string $name): Permission
    {
        $parts = explode('.', $name);

        return Permission::create([
            'name' => $name,
            'group' => $parts[1] ?? 'misc',
            'label_key' => "permissions.access.{$parts[1]}.index",
        ]);
    }

    private function actingAsUserWith(string ...$permissionNames): User
    {
        $role = Role::create(['name' => 'test-role', 'is_system' => false]);
        foreach ($permissionNames as $name) {
            $role->permissions()->attach($this->permission($name)->id);
        }

        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_super_admin_bypasses_all_checks(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertOk();
    }

    public function test_user_without_roles_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);

        $this->actingAs($user)->get('/admin/products')->assertForbidden();
    }

    public function test_user_with_permission_can_access_that_section_only(): void
    {
        $user = $this->actingAsUserWith('admin.products.index');

        $this->actingAs($user)->get('/admin/products')->assertOk();
        $this->actingAs($user)->get('/admin/blogs')->assertForbidden();
        $this->actingAs($user)->get('/admin/roles')->assertForbidden();
    }

    public function test_free_routes_are_accessible_to_any_authenticated_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);

        // dashboard در لیست free است (نمای کلی) — حتی بدون نقش
        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
    }

    public function test_permission_catalog_matches_config_tree(): void
    {
        $expected = [];
        foreach (config('permissions.permissions') as $group => $cfg) {
            foreach (array_keys($cfg['access']) as $access) {
                $expected[] = "admin.{$group}.{$access}";
            }
        }

        foreach ($expected as $name) {
            $this->permission($name);
        }

        $this->assertCount(count($expected), Permission::all());
    }

    public function test_role_permission_change_invalidates_cache(): void
    {
        $role = Role::create(['name' => 'dynamic-role', 'is_system' => false]);
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->attach($role);

        // ابتدا بدون دسترسی → 403
        $this->actingAs($user)->get('/admin/products')->assertForbidden();

        // دادن دسترسی و bump نسخه کش → دسترسی فوری
        $role->permissions()->attach($this->permission('admin.products.index')->id);
        User::bumpPermissionsVersion();

        $this->actingAs($user)->get('/admin/products')->assertOk();
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'main', 'is_system' => true]);
        $user = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($user)->delete("/admin/roles/{$role->id}")->assertForbidden();
    }

    public function test_last_active_super_admin_cannot_be_deactivated(): void
    {
        $super = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->actingAs($super);

        $this->put("/admin/users/{$super->id}", [
            'name' => $super->name,
            'mobile' => '09120000001',
            'email' => $super->email,
            'is_admin' => 1,
            'is_active' => 0,
        ])->assertSessionHasErrors('is_admin');

        $this->assertDatabaseHas('users', ['id' => $super->id, 'is_active' => 1]);
    }

    public function test_non_super_admin_cannot_edit_super_admin(): void
    {
        $super = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $editor = $this->actingAsUserWith('admin.users.edit');

        $this->actingAs($editor)
            ->get("/admin/users/{$super->id}/edit")
            ->assertForbidden();

        $this->actingAs($editor)
            ->put("/admin/users/{$super->id}", ['name' => 'hacked', 'mobile' => '09120000002'])
            ->assertForbidden();
    }
}
