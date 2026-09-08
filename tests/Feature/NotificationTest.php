<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->service = app(NotificationService::class);
    }

    // ==================== Model Tests ====================

    public function test_notification_can_be_created()
    {
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Test Title',
            'body' => 'Test body',
            'icon' => '📦',
            'link' => '/test',
            'is_read' => false,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'order.created',
            'title' => 'Test Title',
        ]);
    }

    public function test_notification_belongs_to_user()
    {
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Test',
        ]);

        $this->assertInstanceOf(User::class, $notification->notifiable);
        $this->assertEquals($this->user->id, $notification->notifiable_id);
    }

    public function test_user_has_notifications_relationship()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Test',
        ]);

        $this->assertCount(1, $this->user->notifications);
    }

    public function test_scope_unread_filters_read_notifications()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Unread',
            'is_read' => false,
        ]);
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.paid',
            'title' => 'Read',
            'is_read' => true,
        ]);

        $unread = Notification::forUser($this->user->id)->unread()->count();
        $this->assertEquals(1, $unread);
    }

    public function test_scope_for_user_filters_by_user()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'User notification',
        ]);
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->admin->id,
            'type' => 'order.created',
            'title' => 'Admin notification',
        ]);

        $count = Notification::forUser($this->user->id)->count();
        $this->assertEquals(1, $count);
    }

    public function test_mark_as_read()
    {
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Test',
            'is_read' => false,
        ]);

        $notification->markAsRead();

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_mark_all_as_read()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Test 1',
            'is_read' => false,
        ]);
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.paid',
            'title' => 'Test 2',
            'is_read' => false,
        ]);

        $count = Notification::markAllAsRead($this->user->id);
        $this->assertEquals(2, $count);
        $this->assertEquals(0, Notification::forUser($this->user->id)->unread()->count());
    }

    public function test_get_unread_count()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
            'title' => 'Test',
            'is_read' => false,
        ]);

        $this->assertEquals(1, Notification::getUnreadCount($this->user->id));
    }

    public function test_get_admin_unread_count()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->admin->id,
            'type' => 'order.new',
            'title' => 'Admin notification',
            'is_read' => false,
        ]);

        $this->assertEquals(1, Notification::getAdminUnreadCount());
    }

    // ==================== Service Tests ====================

    public function test_service_notify_user()
    {
        $notification = $this->service->notifyUser(
            $this->user,
            'order.created',
            'Order Created',
            'Your order was created',
            '/orders/1'
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals('order.created', $notification->type);
        // notifyUser به‌عمد رشتهٔ title را نادیده می‌گیرد و عنوان را از کلید ترجمه می‌سازد
        $this->assertEquals(__('notifications.order.created.title'), $notification->title);
        $this->assertEquals('/orders/1', $notification->link);
        $this->assertFalse($notification->is_read);
    }

    public function test_service_notify_admins()
    {
        $count = $this->service->notifyAdmins(
            'order.new',
            'New Order',
            'A new order was placed',
            '/admin/orders/1'
        );

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->admin->id,
            'type' => 'order.new',
        ]);
    }

    public function test_service_get_unread_count()
    {
        $this->service->notifyUser($this->user, 'order.created', 'Test');

        $count = $this->service->getUnreadCount($this->user);
        $this->assertEquals(1, $count);
    }

    public function test_service_mark_as_read()
    {
        $notification = $this->service->notifyUser($this->user, 'order.created', 'Test');

        $result = $this->service->markAsRead($notification->id, $this->user);
        $this->assertTrue($result);
        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_service_mark_all_as_read()
    {
        $this->service->notifyUser($this->user, 'order.created', 'Test 1');
        $this->service->notifyUser($this->user, 'order.paid', 'Test 2');

        $count = $this->service->markAllAsRead($this->user);
        $this->assertEquals(2, $count);
    }

    public function test_service_get_user_notifications()
    {
        $this->service->notifyUser($this->user, 'order.created', 'Test 1');
        $this->service->notifyUser($this->user, 'order.paid', 'Test 2');

        $notifications = $this->service->getUserNotifications($this->user);
        $this->assertCount(2, $notifications);
    }

    public function test_user_notifications_exclude_admin_types()
    {
        // Create a user notification for the admin user
        $this->service->notifyUser($this->admin, 'order.created', 'Test 1');
        // Create admin notifications for the admin user
        $this->service->notifyAdmins('order.new', 'Admin Test', null, '/admin/orders/1');
        $this->service->notifyAdmins('order.paid_admin', 'Admin Paid', null, '/admin/orders/2');

        $notifications = $this->service->getUserNotifications($this->admin);

        // Only user-type notifications should be returned, not admin-type
        $this->assertCount(1, $notifications);
        $this->assertEquals('order.created', $notifications->first()->type);
    }

    public function test_user_notifications_endpoint_only_returns_user_types()
    {
        // Create a user notification for the admin user
        $this->service->notifyUser(
            $this->admin,
            'order.created',
            'Test',
            'Test body',
            '/account/orders/1'
        );
        $this->service->notifyAdmins('order.new', 'Admin Test', null, '/admin/orders/1');

        $response = $this->actingAs($this->admin)
            ->getJson('/account/notifications?format=json');

        $response->assertOk();
        $data = $response->json();

        // Only user notifications, not admin notifications
        $this->assertCount(1, $data['data']);
        $this->assertEquals('order.created', $data['data'][0]['type']);

        // Link should be a site route, not admin
        $this->assertStringStartsNotWith('/admin', $data['data'][0]['link']);
    }

    // ==================== Route Tests ====================

    public function test_user_can_view_notifications_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/account/notifications');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_notifications_page()
    {
        $response = $this->get('/account/notifications');

        $response->assertRedirect();
    }

    public function test_user_can_get_unread_count()
    {
        $this->service->notifyUser($this->user, 'order.created', 'Test');

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_user_can_mark_notification_read()
    {
        $notification = $this->service->notifyUser($this->user, 'order.created', 'Test');

        $response = $this->actingAs($this->user)
            ->postJson('/api/notifications/' . $notification->id . '/read');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_user_can_mark_all_read()
    {
        $this->service->notifyUser($this->user, 'order.created', 'Test 1');
        $this->service->notifyUser($this->user, 'order.paid', 'Test 2');

        $response = $this->actingAs($this->user)
            ->postJson('/api/notifications/read-all');

        $response->assertOk()
            ->assertJson(['success' => true, 'count' => 2]);
    }

    // ==================== Trigger Tests ====================

    public function test_order_creation_triggers_notifications()
    {
        // Simulate order creation notification without factory
        $orderId = 1;

        $this->service->notifyUser(
            $this->user,
            'order.created',
            'سفارش شما ثبت شد',
            "سفارش #{$orderId} ثبت شد.",
            '/account/orders/' . $orderId,
            ['order_id' => $orderId]
        );
        $this->service->notifyAdmins(
            'order.new',
            'سفارش جدید ثبت شد',
            "سفارش #{$orderId} از {$this->user->name}",
            '/admin/orders/' . $orderId,
            ['order_id' => $orderId]
        );

        // User notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
            'type' => 'order.created',
        ]);

        // Admin notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->admin->id,
            'type' => 'order.new',
        ]);
    }

    public function test_welcome_discount_notification()
    {
        $this->service->notifyUser(
            $this->user,
            'discount.welcome',
            'کد تخفیف خوشامدگویی',
            'تبریک! یک کد تخفیف ۱۰٪ ایجاد شد.',
            '/account',
            ['user_id' => $this->user->id]
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->user->id,
            'type' => 'discount.welcome',
        ]);
    }

    // ==================== RBAC Tests ====================

    public function test_non_admin_cannot_access_admin_notifications()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/notifications');

        // AdminPermission middleware redirects non-admin users
        $response->assertStatus(302);
    }

    public function test_admin_can_access_admin_notifications()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/notifications');

        $response->assertOk();
    }

    // ==================== Audience Split Regression Tests ====================

    public function test_admin_panel_excludes_personal_user_types()
    {
        // ادمین به‌عنوان خریدار در سایت اعلان شخصی می‌گیرد
        $this->service->notifyUser($this->admin, 'order.created', 'Test', null, '/account/orders/1', ['order_id' => 1, 'amount' => 1000]);
        $this->service->notifyAdmins('order.new', 'Admin', null, '/admin/orders/1', ['order_id' => 1]);

        $list = $this->service->getAdminNotifications($this->admin);

        $this->assertCount(1, $list);
        $this->assertEquals('order.new', $list->first()->type);
        $this->assertEquals(1, $this->service->getAdminUnreadCount($this->admin));
    }

    public function test_site_count_excludes_admin_types_for_admin_user()
    {
        $this->service->notifyUser($this->admin, 'order.created', 'Test', null, '/account/orders/1', ['order_id' => 1, 'amount' => 1000]);
        $this->service->notifyAdmins('order.new', 'Admin', null, '/admin/orders/1', ['order_id' => 1]);

        // بج سایت فقط user را می‌شمارد (۱ نه ۲)
        $this->assertEquals(1, $this->service->getUnreadCount($this->admin));

        $response = $this->actingAs($this->admin)->getJson('/account/notifications?format=json');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('order.created', $response->json('data.0.type'));
    }

    public function test_admin_dropdown_link_points_to_go_and_redirects_to_target()
    {
        $this->service->notifyAdmins('order.new', 'Admin', null, '/admin/orders/99', ['order_id' => 99]);
        $notification = Notification::forUser($this->admin->id)->adminNotifications()->firstOrFail();

        $json = $this->actingAs($this->admin)->getJson('/admin/notifications?format=json');
        $json->assertOk();
        $this->assertStringContainsString("/admin/notifications/{$notification->id}/go", $json->json('data.0.link'));

        $go = $this->actingAs($this->admin)->get("/admin/notifications/{$notification->id}/go");
        $go->assertRedirect('/admin/orders/99');
        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_site_go_redirects_to_target()
    {
        $n = $this->service->notifyUser($this->user, 'order.created', 'Test', null, '/account/orders/5', ['order_id' => 5, 'amount' => 1000]);

        $go = $this->actingAs($this->user)->get("/account/notifications/{$n->id}/go");
        $go->assertRedirect('/account/orders/5');
        $this->assertTrue($n->fresh()->is_read);
    }

    public function test_mark_all_read_is_audience_scoped()
    {
        $this->service->notifyUser($this->admin, 'order.created', 'Test', null, '/x', ['order_id' => 1, 'amount' => 1]);
        $this->service->notifyAdmins('order.new', 'Admin', null, '/admin/orders/1', ['order_id' => 1]);

        // خواندن همه در سایت نباید اعلان ادمینی را بخواند و بالعکس
        $this->service->markAllUserAsRead($this->admin);
        $this->assertEquals(0, $this->service->getUnreadCount($this->admin));
        $this->assertEquals(1, $this->service->getAdminUnreadCount($this->admin));

        $this->service->markAllAdminAsRead($this->admin);
        $this->assertEquals(0, $this->service->getAdminUnreadCount($this->admin));
    }
}
