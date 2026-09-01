<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDiscountCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);

        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    private function createDiscount(array $overrides = []): Discount
    {
        return Discount::create(array_merge([
            'title' => 'تخفیف تست',
            'code' => 'TEST' . strtoupper(uniqid()),
            'amount' => 10,
            'type' => 'percent',
            'count' => 100,
            'max_usage_per_user' => 1,
            'is_active' => true,
        ], $overrides));
    }

    // ── Index ──

    public function test_index_page_renders(): void
    {
        $this->actingAs($this->admin)->get(route('admin.discounts.index'))
            ->assertOk()
            ->assertSee('کدهای تخفیف');
    }

    public function test_index_shows_existing_discounts(): void
    {
        $discount = $this->createDiscount(['code' => 'SHOWME']);

        $this->actingAs($this->admin)->get(route('admin.discounts.index'))
            ->assertOk()
            ->assertSee('SHOWME');
    }

    public function test_index_search_filters_by_title_and_code(): void
    {
        $this->createDiscount(['title' => 'تخفیف ویژه', 'code' => 'SPECIAL']);
        $this->createDiscount(['title' => 'تخفیف عادی', 'code' => 'NORMAL']);

        $this->actingAs($this->admin)->get(route('admin.discounts.index', ['search' => 'SPECIAL']))
            ->assertOk()
            ->assertSee('SPECIAL')
            ->assertDontSee('NORMAL');
    }

    public function test_index_status_filter(): void
    {
        $this->createDiscount(['code' => 'ACTIVE1', 'is_active' => true]);
        $this->createDiscount(['code' => 'INACTIVE1', 'is_active' => false]);

        $this->actingAs($this->admin)->get(route('admin.discounts.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('ACTIVE1')
            ->assertDontSee('INACTIVE1');
    }

    // ── Create ──

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->admin)->get(route('admin.discounts.create'))
            ->assertOk()
            ->assertSee('افزودن کد تخفیف جدید');
    }

    public function test_store_creates_discount(): void
    {
        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'کد جدید',
            'code' => 'NEWCODE',
            'amount' => 15,
            'type' => 'percent',
            'count' => 50,
            'max_usage_per_user' => 2,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('discounts', [
            'code' => 'NEWCODE',
            'amount' => 15,
            'type' => 'percent',
            'count' => 50,
        ]);
    }

    public function test_store_with_category_discountable(): void
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'دسته تست'],
            'slug' => 'test-cat',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'کد دسته‌بندی',
            'code' => 'CATONLY',
            'amount' => 10,
            'type' => 'percent',
            'count' => 50,
            'max_usage_per_user' => 1,
            'pay_type' => 'category',
            'discountable_ids' => [$category->id],
            'is_active' => true,
        ])->assertRedirect();

        $discount = Discount::where('code', 'CATONLY')->first();
        $this->assertNotNull($discount);
        $this->assertTrue($discount->categories->contains($category->id));
    }

    public function test_store_with_brand_discountable(): void
    {
        $brand = Brand::create([
            'title' => ['fa' => 'برند تست'],
            'url' => 'test-brand-' . uniqid(),
            'active' => true,
        ]);

        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'کد برند',
            'code' => 'BRANDONLY',
            'amount' => 50000,
            'type' => 'cash',
            'count' => 10,
            'max_usage_per_user' => 1,
            'pay_type' => 'brand',
            'discountable_ids' => [$brand->id],
            'is_active' => true,
        ])->assertRedirect();

        $discount = Discount::where('code', 'BRANDONLY')->first();
        $this->assertNotNull($discount);
        $this->assertTrue($discount->brands->contains($brand->id));
    }

    public function test_store_with_product_discountable(): void
    {
        $category = ProductCategory::create([
            'name' => ['fa' => 'Cat'],
            'slug' => 'cat',
            'is_active' => true,
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => ['fa' => 'محصول'],
            'slug' => 'prod-' . uniqid(),
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'کد محصول',
            'code' => 'PRODONLY',
            'amount' => 25,
            'type' => 'percent',
            'count' => 10,
            'max_usage_per_user' => 1,
            'pay_type' => 'product',
            'discountable_ids' => [$product->id],
            'is_active' => true,
        ])->assertRedirect();

        $discount = Discount::where('code', 'PRODONLY')->first();
        $this->assertNotNull($discount);
        $this->assertTrue($discount->products->contains($product->id));
    }

    public function test_store_validation_requires_title(): void
    {
        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'code' => 'NOTITLE',
            'amount' => 10,
            'type' => 'percent',
            'count' => 10,
            'max_usage_per_user' => 1,
            'is_active' => true,
        ])->assertSessionHasErrors('title');
    }

    public function test_store_validation_requires_unique_code(): void
    {
        $this->createDiscount(['code' => 'DUPLICATE']);

        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'Test',
            'code' => 'DUPLICATE',
            'amount' => 10,
            'type' => 'percent',
            'count' => 10,
            'max_usage_per_user' => 1,
            'is_active' => true,
        ])->assertSessionHasErrors('code');
    }

    public function test_store_validation_percent_max_100(): void
    {
        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'Over 100',
            'code' => 'OVER100',
            'amount' => 150,
            'type' => 'percent',
            'count' => 10,
            'max_usage_per_user' => 1,
            'is_active' => true,
        ])->assertSessionHasErrors('amount');
    }

    public function test_store_requires_discountable_ids_when_pay_type_selected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.discounts.store'), [
            'title' => 'No IDs',
            'code' => 'NOIDS',
            'amount' => 10,
            'type' => 'percent',
            'count' => 10,
            'max_usage_per_user' => 1,
            'pay_type' => 'category',
            'discountable_ids' => [],
            'is_active' => true,
        ])->assertSessionHasErrors('discountable_ids');
    }

    // ── Show ──

    public function test_show_page_renders(): void
    {
        $discount = $this->createDiscount();

        $this->actingAs($this->admin)->get(route('admin.discounts.show', $discount))
            ->assertOk()
            ->assertSee($discount->code);
    }

    // ── Edit ──

    public function test_edit_page_renders(): void
    {
        $discount = $this->createDiscount();

        $this->actingAs($this->admin)->get(route('admin.discounts.edit', $discount))
            ->assertOk()
            ->assertSee('ویرایش کد تخفیف');
    }

    public function test_update_modifies_discount(): void
    {
        $discount = $this->createDiscount(['code' => 'ORIG']);

        $this->actingAs($this->admin)->put(route('admin.discounts.update', $discount), [
            'title' => 'عنوان جدید',
            'code' => 'ORIG',
            'amount' => 25,
            'type' => 'cash',
            'count' => 200,
            'max_usage_per_user' => 5,
            'is_active' => true,
        ])->assertRedirect();

        $discount->refresh();
        $this->assertEquals('عنوان جدید', $discount->title);
        $this->assertEquals(25, $discount->amount);
        $this->assertEquals('cash', $discount->type);
    }

    public function test_update_can_change_discountable(): void
    {
        $cat1 = ProductCategory::create(['name' => ['fa' => 'Cat1'], 'slug' => 'c1', 'is_active' => true]);
        $cat2 = ProductCategory::create(['name' => ['fa' => 'Cat2'], 'slug' => 'c2', 'is_active' => true]);

        $discount = $this->createDiscount([
            'code' => 'SWITCH',
            'pay_type' => 'category',
        ]);
        $discount->categories()->attach($cat1->id);

        $this->actingAs($this->admin)->put(route('admin.discounts.update', $discount), [
            'title' => 'Updated',
            'code' => 'SWITCH',
            'amount' => 10,
            'type' => 'percent',
            'count' => 50,
            'max_usage_per_user' => 1,
            'pay_type' => 'category',
            'discountable_ids' => [$cat2->id],
            'is_active' => true,
        ])->assertRedirect();

        $discount->refresh();
        $this->assertTrue($discount->categories->contains($cat2->id));
        $this->assertFalse($discount->categories->contains($cat1->id));
    }

    // ── Destroy ──

    public function test_destroy_soft_deletes_discount(): void
    {
        $discount = $this->createDiscount();

        $this->actingAs($this->admin)->delete(route('admin.discounts.destroy', $discount))
            ->assertRedirect();

        $this->assertSoftDeleted('discounts', ['id' => $discount->id]);
    }

    // ── Toggle Status ──

    public function test_toggle_status_toggles_active_flag(): void
    {
        $discount = $this->createDiscount(['is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.discounts.toggle-status', $discount))
            ->assertJson(['success' => true, 'is_active' => false]);

        $discount->refresh();
        $this->assertFalse($discount->is_active);
    }

    public function test_toggle_status_back_to_active(): void
    {
        $discount = $this->createDiscount(['is_active' => false]);

        $this->actingAs($this->admin)->post(route('admin.discounts.toggle-status', $discount))
            ->assertJson(['success' => true, 'is_active' => true]);

        $discount->refresh();
        $this->assertTrue($discount->is_active);
    }

    // ── Guest / non-admin ──

    public function test_guest_cannot_access_discounts(): void
    {
        $this->get(route('admin.discounts.index'))->assertRedirect();
    }
}
