<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCrudTest extends TestCase
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

    private function superAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_creating_role_expands_same_label_permissions(): void
    {
        // دسترسی‌های کاتالوگ را طوری می‌سازیم که گویی سیدر ساخته است
        foreach (config('permissions.permissions') as $group => $cfg) {
            foreach (array_keys($cfg['access']) as $access) {
                $this->permission("admin.{$group}.{$access}");
            }
        }

        $this->actingAs($this->superAdmin())
            ->post('/admin/roles', [
                'name' => 'content-editor',
                'permissions' => ['admin.products.create'], // همان لیبل با store
            ])
            ->assertRedirect();

        $role = Role::where('name', 'content-editor')->firstOrFail();
        $names = $role->permissions->pluck('name')->sort()->values();

        // تیک «افزودن» باید create+store هر دو را sync کند
        $this->assertTrue($names->contains('admin.products.create'));
        $this->assertTrue($names->contains('admin.products.store'));
        $this->assertFalse($names->contains('admin.products.destroy'));
    }

    public function test_creating_role_with_duplicate_name_is_rejected(): void
    {
        Role::create(['name' => 'main', 'is_system' => true]);

        $this->actingAs($this->superAdmin())
            ->post('/admin/roles', ['name' => 'main'])
            ->assertSessionHasErrors('name');
    }

    public function test_non_super_admin_cannot_edit_system_role(): void
    {
        $main = Role::create(['name' => 'main', 'is_system' => true]);

        // editor دسترسی admin.roles.edit دارد (میدلور عبور می‌کند)
        // — 403 باید از authorizeSystemRole در کنترلر بیاید، نه از میدلور
        $editorRole = Role::create(['name' => 'editor-role']);
        $editorRole->permissions()->attach($this->permission('admin.roles.edit')->id);

        $editor = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $editor->roles()->attach($editorRole);

        // کنترل: editor می‌تواند نقش عادی را ویرایش کند
        $normalRole = Role::create(['name' => 'normal-role']);
        $this->actingAs($editor)->get("/admin/roles/{$normalRole->id}/edit")->assertOk();

        // ولی نقش سیستمی را نه (از authorizeSystemRole)
        $this->actingAs($editor)
            ->get("/admin/roles/{$main->id}/edit")
            ->assertForbidden();
    }

    public function test_updating_user_with_role_via_http_grants_access(): void
    {
        // کاتالوگ کامل (مثل سیدر)
        foreach (config('permissions.permissions') as $group => $cfg) {
            foreach (array_keys($cfg['access']) as $access) {
                $this->permission("admin.{$group}.{$access}");
            }
        }

        $role = Role::create(['name' => 'main', 'is_system' => true]);
        $role->permissions()->sync(Permission::pluck('id'));

        $super = $this->superAdmin();
        $target = User::factory()->create(['is_admin' => false, 'is_active' => true]);

        $this->actingAs($super)
            ->put("/admin/users/{$target->id}", [
                'name' => $target->name,
                'mobile' => $target->mobile,
                'email' => $target->email,
                'roles' => [$role->id],
                'is_active' => 1,
            ])
            ->assertRedirect();

        $target->refresh();
        $this->assertTrue($target->roles->contains('id', $role->id));
        $this->assertTrue($target->canAccess('admin.products.index'));
    }

    public function test_super_admin_can_edit_system_role(): void
    {
        $main = Role::create(['name' => 'main', 'is_system' => true]);

        $this->actingAs($this->superAdmin())
            ->get("/admin/roles/{$main->id}/edit")
            ->assertOk();
    }
}
