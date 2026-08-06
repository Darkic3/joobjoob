<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductCategoryCrudTest extends TestCase
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

    private function categoryPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => ['fa' => 'موبایل و لوازم جانبی', 'en' => 'Mobile & Accessories'],
            'slug' => 'mobile-accessories',
            'sort_order' => 1,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_index_requires_product_categories_permission(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/product-categories')->assertOk();

        $role = Role::create(['name' => 'no-cat', 'is_system' => false]);
        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->attach($role);

        $this->actingAs($user)->get('/admin/product-categories')->assertForbidden();
    }

    public function test_inactive_filter_works(): void
    {
        // اسلاگ‌ها عمداً بدون زیررشته مشترک انتخاب شده‌اند (کلمه active در کلاس‌های CSS هم هست)
        ProductCategory::create(['name' => ['fa' => 'فعال', 'en' => 'Active'], 'slug' => 'cat-on', 'is_active' => true]);
        ProductCategory::create(['name' => ['fa' => 'غیرفعال', 'en' => 'Inactive'], 'slug' => 'cat-off', 'is_active' => false]);

        $html = $this->actingAs($this->superAdmin())
            ->get('/admin/product-categories?is_active=0')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('cat-off', $html);
        $this->assertStringNotContainsString('cat-on', $html);
    }

    public function test_search_matches_translated_name(): void
    {
        ProductCategory::create(['name' => ['fa' => 'لوازم جانبی موبایل', 'en' => 'Mobile Accessories'], 'slug' => 'mobile-accessories', 'is_active' => true]);
        ProductCategory::create(['name' => ['fa' => 'لپ‌تاپ', 'en' => 'Laptops'], 'slug' => 'laptops', 'is_active' => true]);

        $html = $this->actingAs($this->superAdmin())
            ->get('/admin/product-categories?search=موبایل')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('mobile-accessories', $html);
        $this->assertStringNotContainsString('laptops', $html);
    }

    public function test_create_with_empty_file_input_is_rejected_gracefully(): void
    {
        // مرورگرهای واقعی حتی وقتی کاربر تصویری انتخاب نمی‌کند، فیلد فایل خالی را با error=4 ارسال می‌کنند.
        // لاراول این را به‌جای کرش (خطای قبلی «Path cannot be empty») با خطای ولیدیشن «uploaded» رد می‌کند.
        $emptyFile = new UploadedFile('', 'dummy.png', 'image/png', UPLOAD_ERR_NO_FILE, true);

        $this->actingAs($this->superAdmin())
            ->post('/admin/product-categories', $this->categoryPayload(['image' => $emptyFile]))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('product_categories', ['slug' => 'mobile-accessories']);
    }

    public function test_update_with_empty_file_input_keeps_existing_image(): void
    {
        $category = ProductCategory::create(['name' => ['fa' => 'قدیمی', 'en' => 'Old'], 'slug' => 'old-slug', 'image' => 'keep.webp', 'is_active' => true]);
        $emptyFile = new UploadedFile('', 'dummy.png', 'image/png', UPLOAD_ERR_NO_FILE, true);

        $this->actingAs($this->superAdmin())
            ->put("/admin/product-categories/{$category->id}", $this->categoryPayload(['image' => $emptyFile]))
            ->assertSessionHasErrors('image');

        $this->assertSame('keep.webp', $category->refresh()->image);
    }

    public function test_update_can_remove_current_image(): void
    {
        $category = ProductCategory::create(['name' => ['fa' => 'دارای تصویر', 'en' => 'With Image'], 'slug' => 'with-image', 'image' => 'old.webp', 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/product-categories/{$category->id}", $this->categoryPayload(['remove_image' => '1']))
            ->assertRedirect(route('admin.product-categories.index'));

        $this->assertNull($category->refresh()->image);
    }

    public function test_super_admin_can_create_product_category_via_http(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/product-categories', $this->categoryPayload())
            ->assertRedirect(route('admin.product-categories.index'));

        $category = ProductCategory::where('slug', 'mobile-accessories')->firstOrFail();
        // assertEquals: ترتیب کلیدهای JSON در MySQL حروفی می‌شود (en قبل از fa)
        $this->assertEquals(['fa' => 'موبایل و لوازم جانبی', 'en' => 'Mobile & Accessories'], $category->name);
        $this->assertTrue($category->is_active);
        $this->assertSame(1, $category->sort_order);
    }

    public function test_creating_category_without_english_name_is_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/product-categories', $this->categoryPayload([
                'name' => ['fa' => 'فقط فارسی'],
            ]))
            ->assertSessionHasErrors('name.en');
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        ProductCategory::create(['name' => ['fa' => 'اول', 'en' => 'One'], 'slug' => 'mobile-accessories', 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->post('/admin/product-categories', $this->categoryPayload())
            ->assertSessionHasErrors('slug');
    }

    public function test_super_admin_can_update_category(): void
    {
        $category = ProductCategory::create(['name' => ['fa' => 'قدیمی', 'en' => 'Old'], 'slug' => 'old-slug', 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->put("/admin/product-categories/{$category->id}", $this->categoryPayload(['slug' => 'new-slug']))
            ->assertRedirect(route('admin.product-categories.index'));

        $category->refresh();
        $this->assertSame('new-slug', $category->slug);
        $this->assertSame('موبایل و لوازم جانبی', $category->name['fa']);
    }

    public function test_destroy_is_blocked_when_category_has_products(): void
    {
        $category = ProductCategory::create(['name' => ['fa' => 'دارای محصول', 'en' => 'With Products'], 'slug' => 'with-products', 'is_active' => true]);

        // ستون product_category_id محصولات cascadeOnDelete است — حذف دسته = حذف محصولاتش
        Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'slug' => 'test-product',
            'price' => 10000,
            'stock' => 5,
        ]);

        $this->actingAs($this->superAdmin())
            ->delete("/admin/product-categories/{$category->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('product_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['slug' => 'test-product']);
    }

    public function test_destroy_is_blocked_when_category_has_children(): void
    {
        $parent = ProductCategory::create(['name' => ['fa' => 'والد', 'en' => 'Parent'], 'slug' => 'parent', 'is_active' => true]);
        ProductCategory::create(['parent_id' => $parent->id, 'name' => ['fa' => 'زیر', 'en' => 'Child'], 'slug' => 'child', 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->delete("/admin/product-categories/{$parent->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('product_categories', ['id' => $parent->id]);
    }

    public function test_destroy_works_for_empty_category(): void
    {
        $category = ProductCategory::create(['name' => ['fa' => 'خالی', 'en' => 'Empty'], 'slug' => 'empty', 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->delete("/admin/product-categories/{$category->id}")
            ->assertRedirect(route('admin.product-categories.index'));

        // حذف نرم (SoftDeletes) است
        $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
    }
}
