<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCrudTest extends TestCase
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

    private function userWithPermission(string ...$permissionNames): User
    {
        $role = Role::create(['name' => 'user-creator-'.uniqid(), 'is_system' => false]);
        foreach ($permissionNames as $name) {
            $role->permissions()->attach($this->permission($name)->id);
        }

        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_create_page_requires_users_create_permission(): void
    {
        $this->actingAs($this->userWithPermission('admin.users.edit'))
            ->get('/admin/users/create')
            ->assertForbidden();

        $this->actingAs($this->userWithPermission('admin.users.create'))
            ->get('/admin/users/create')
            ->assertOk();

        $this->actingAs($this->superAdmin())
            ->get('/admin/users/create')
            ->assertOk();
    }

    public function test_super_admin_can_create_user_via_http(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/users', [
                'name' => 'کاربر جدید',
                'mobile' => '09135556677',
                'email' => 'new@shopora.test',
                'password' => 'secret123',
            ])
            ->assertRedirect();

        $user = User::where('email', 'new@shopora.test')->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertFalse($user->is_admin); // پیش‌فرض عادی
        $this->assertTrue($user->is_active); // پیش‌فرض فعال
    }

    public function test_creating_user_without_password_is_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/users', [
                'name' => 'بدون رمز',
                'mobile' => '09135556688',
                'email' => 'nopass@shopora.test',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_creating_user_with_duplicate_mobile_is_rejected(): void
    {
        $existing = User::factory()->create(['is_admin' => false, 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->post('/admin/users', [
                'name' => 'تکراری',
                'mobile' => $existing->mobile,
                'email' => 'dup@shopora.test',
                'password' => 'secret123',
            ])
            ->assertSessionHasErrors('mobile');
    }

    public function test_non_super_admin_cannot_create_super_admin(): void
    {
        // در UI، تیک «افزودن کاربر» هر دو create+store را با هم می‌دهد (dedupe)
        $creator = $this->userWithPermission('admin.users.create', 'admin.users.store');

        // حتی اگر is_admin=1 پست کند، به‌زور کاربر عادی و فعال ساخته می‌شود
        $this->actingAs($creator)
            ->post('/admin/users', [
                'name' => 'هکر',
                'mobile' => '09135556699',
                'email' => 'hacker@shopora.test',
                'password' => 'secret123',
                'is_admin' => 1,
                'is_active' => 0,
            ])
            ->assertRedirect();

        $user = User::where('email', 'hacker@shopora.test')->firstOrFail();
        $this->assertFalse($user->is_admin);
        $this->assertTrue($user->is_active);
    }

    public function test_super_admin_can_create_super_admin(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/users', [
                'name' => 'مدیر دوم',
                'mobile' => '09135556600',
                'email' => 'admin2@shopora.test',
                'password' => 'secret123',
                'is_admin' => 1,
            ])
            ->assertRedirect();

        $user = User::where('email', 'admin2@shopora.test')->firstOrFail();
        $this->assertTrue($user->is_admin);
    }

    public function test_non_super_admin_cannot_assign_system_role(): void
    {
        // نقش سیستمی با همه دسترسی‌ها — مثل main واقعی
        $main = Role::create(['name' => 'main', 'is_system' => true]);
        $normalRole = Role::create(['name' => 'editor', 'is_system' => false]);

        $creator = $this->userWithPermission('admin.users.create', 'admin.users.store');

        $this->actingAs($creator)
            ->post('/admin/users', [
                'name' => 'کاربر با نقش',
                'mobile' => '09135556611',
                'email' => 'withrole@shopora.test',
                'password' => 'secret123',
                'roles' => [$main->id, $normalRole->id],
            ])
            ->assertRedirect();

        $user = User::where('email', 'withrole@shopora.test')->firstOrFail();

        // نقش سیستمی حذف شده، فقط نقش عادی وصل است → جلوگیری از ارتقاء دسترسی
        $this->assertFalse($user->roles->contains('id', $main->id));
        $this->assertTrue($user->roles->contains('id', $normalRole->id));
    }
}
