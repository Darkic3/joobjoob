<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * صفحه لیست اعلانات کاربر
     * اگر ?format=json یا Accept: application/json باشد، لیست JSON برای دراپ‌داون هدر برمی‌گرداند
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationService->getUserNotifications(Auth::user());

        if ($request->get('format') === 'json' || $request->wantsJson()) {
            return response()->json([
                'data' => collect($notifications->items())->map(function ($n) {
                    $id = $n->id ?? null;
                    return [
                        'id' => $id,
                        'type' => $n->type,
                        'title' => $n->title,
                        'body' => $n->body,
                        'icon' => $n->icon,
                        // کلیک مستقیم به مقصد (go) — مثل پنل ادمین
                        'link' => $id !== null ? route('dashboard.notification-go', $id) : null,
                        'target' => $n->link,
                        'is_read' => (bool) $n->is_read,
                        'created_at' => $n->created_at?->diffForHumans(),
                    ];
                })->values(),
                'count' => $this->notificationService->getUnreadCount(Auth::user()),
            ]);
        }

        return view('site.dashboard.notifications', compact('notifications'));
    }

    /**
     * نمایش جزئیات یک اعلان
     */
    public function show(int $id)
    {
        $notification = $this->notificationService->getUserNotifications(Auth::user())
            ->getCollection()
            ->firstWhere('id', $id);

        if (! $notification) {
            return redirect()->route('dashboard.notifications')
                ->with('error', 'اعلان یافت نشد.');
        }

        // علامت‌گذاری به عنوان خوانده‌شده
        $this->notificationService->markAsRead($id, Auth::user());

        return view('site.dashboard.notification-detail', compact('notification'));
    }

    /**
     * رفتن به مقصد اعلان + علامت‌گذاری خوانده‌شده (سمت سرور — قرینه go پنل ادمین)
     */
    public function go(int $id)
    {
        $user = Auth::user();
        $this->notificationService->markAsRead($id, $user);

        $notification = $this->notificationService->getUserNotifications($user)
            ->getCollection()
            ->firstWhere('id', $id);

        $target = $notification?->link ?: route('dashboard.notifications');

        // فقط مسیرهای داخلی — جلوگیری از open-redirect
        if (! str_starts_with($target, '/') && ! str_starts_with($target, url('/'))) {
            $target = route('dashboard.notifications');
        }

        return redirect()->to($target);
    }

    /**
     * تعداد اعلان‌های خوانده‌نشده (AJAX)
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());

        return response()->json(['count' => $count]);
    }

    /**
     * خوانده‌شده یک اعلان (AJAX)
     */
    public function markRead(int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id, Auth::user());

        return response()->json(['success' => $success]);
    }

    /**
     * خوانده‌شده همه اعلان‌ها (AJAX)
     */
    public function markAllRead(): JsonResponse
    {
        $count = $this->notificationService->markAllUserAsRead(Auth::user());

        return response()->json(['success' => true, 'count' => $count]);
    }
}
