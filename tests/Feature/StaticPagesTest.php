<?php

namespace Tests\Feature;

use App\Models\ContactRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    // ==========================================
    // رندر صفحات استاتیک
    // ==========================================

    public function test_all_static_pages_render_with_translated_title(): void
    {
        $pages = ['about', 'contact', 'faq', 'terms', 'privacy'];

        foreach ($pages as $page) {
            $this->get("/page/{$page}")
                ->assertOk()
                ->assertSee(__("messages.pages.{$page}.title"));
        }

        $this->get('/page/unknown-page')->assertNotFound();
    }

    public function test_about_page_shows_story_stats_and_values(): void
    {
        $response = $this->get('/page/about')->assertOk();

        $response->assertSee(__('messages.pages.about.story_title'));
        // خروجی HTML سمت سرور بومی است (ارقام آمار صفحه درباره ما فارسی می‌شود)
        $response->assertSee(\App\Helpers\NumberHelper::localizeText(__('messages.pages.about.stats.0.value')), false);
        $response->assertSee(__('messages.pages.about.values.0.title'));
        $response->assertSee(__('messages.pages.about.values.0.text'));
    }

    public function test_faq_page_shows_question_items(): void
    {
        $this->get('/page/faq')
            ->assertOk()
            ->assertSee(__('messages.pages.faq.items.0.q'))
            ->assertSee(__('messages.pages.faq.items.0.a'));
    }

    public function test_legal_pages_show_sections(): void
    {
        $this->get('/page/terms')
            ->assertOk()
            ->assertSee(__('messages.pages.terms.sections.0.title'));

        $this->get('/page/privacy')
            ->assertOk()
            ->assertSee(__('messages.pages.privacy.sections.0.title'));
    }

    public function test_contact_page_shows_form_and_info_cards(): void
    {
        $this->get('/page/contact')
            ->assertOk()
            ->assertSee(__('messages.pages.contact.form_title'))
            ->assertSee(__('messages.pages.contact.info_title'))
            ->assertSee(__('messages.pages.contact.name'))
            ->assertSee('name="message"', false);
    }

    // ==========================================
    // فرم تماس
    // ==========================================

    public function test_contact_form_stores_message_and_redirects_back(): void
    {
        $payload = [
            'name' => 'نگار احمدی',
            'email' => 'negar@example.com',
            'phone' => '09120000000',
            'department' => 'support',
            'message' => 'سلام، درباره سفارشم سوال داشتم و ممنون میشم راهنمایی کنید.',
        ];

        $this->from('/page/contact')
            ->post('/contact', $payload)
            ->assertRedirect('/page/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_requests', [
            'name' => 'نگار احمدی',
            'email' => 'negar@example.com',
            'department' => 'support',
            'read_at' => null,
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->from('/page/contact')
            ->post('/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_requests', 0);
    }

    // ==========================================
    // صندوق پیام‌های تماس در پنل ادمین
    // ==========================================

    private function adminUserWith(string ...$permissionNames): User
    {
        $role = Role::create(['name' => 'contact-role', 'is_system' => false]);
        foreach ($permissionNames as $name) {
            $parts = explode('.', $name);
            $role->permissions()->attach(Permission::firstOrCreate(
            ['name' => $name],
            ['group' => $parts[1] ?? 'misc',
                'label_key' => "permissions.access.{$parts[1]}.index"]
        )->id);
        }

        $user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_admin_contact_index_requires_permission(): void
    {
        $user = $this->adminUserWith('admin.products.index');

        $this->actingAs($user)->get('/admin/contact')->assertForbidden();
    }

    public function test_admin_can_list_read_and_delete_contact_messages(): void
    {
        $admin = $this->adminUserWith('admin.contact.index', 'admin.contact.show', 'admin.contact.destroy');

        $contact = ContactRequest::create([
            'name' => 'علی رضایی',
            'email' => 'ali@example.com',
            'message' => 'پیام تستی برای بررسی صندوق پیام‌ها',
            'read_at' => null,
        ]);

        $this->actingAs($admin)
            ->get('/admin/contact')
            ->assertOk()
            ->assertSee('علی رضایی');

        // نمایش پیام → خوانده‌شده می‌شود
        $this->actingAs($admin)
            ->get("/admin/contact/{$contact->id}")
            ->assertOk()
            ->assertSee('پیام تستی برای بررسی صندوق پیام‌ها');

        $this->assertNotNull($contact->fresh()->read_at);

        // حذف پیام
        $this->actingAs($admin)
            ->delete("/admin/contact/{$contact->id}")
            ->assertRedirect('/admin/contact');

        $this->assertDatabaseMissing('contact_requests', ['id' => $contact->id]);
    }

    public function test_super_admin_can_access_contact_inbox(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($user)->get('/admin/contact')->assertOk();
    }
}
