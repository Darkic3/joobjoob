<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * صفحه لیست اعلانات ادمین
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter');
        $notifications = $this->notificationService->getAdminNotifications(20, $filter);

        if ($request->get('format') === 'json' || $request->wantsJson()) {
            return response()->json([
                'data' => $notifications->getCollection()->map(function ($n) {
                    $id = $n->id ?? null;
                    return [
                        'id' => $id,
                        'type' => $n->type,
                        'title' => $n->title,
                        'body' => $n->body,
                        'icon' => $n->icon,
                        'link' => $id !== null ? route('admin.notifications.show', $id) : null,
                        'is_read' => (bool) $n->is_read,
                        'created_at' => $n->created_at?->diffForHumans(),
                    ];
                })->values(),
                'count' => $this->notificationService->getAdminUnreadCount(),
            ]);
        }

        return view('admin.notifications.index', compact('notifications', 'filter'));
    }

    /**
     * نمایش جزئیات یک اعلان
     */
    public function show(Request $request, int $id)
    {
        $notification = Notification::forUser($request->user()->id)->find($id);

        if (! $notification) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'اعلان یافت نشد.');
        }

        // علامت‌گذاری به عنوان خوانده‌شده
        $notification->markAsRead();

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * تعداد اعلان‌های خوانده‌نشده (AJAX)
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getAdminUnreadCount();

        return response()->json(['count' => $count]);
    }

    /**
     * رفتن به مقصد اعلان + علامت‌گذاری خوانده‌شده (سمت سرور — بدون ریسک لغو AJAX هنگام ناوبری)
     */
    public function go(Request $request, int $id)
    {
        $this->notificationService->markAsRead($id, $request->user());

        $notification = Notification::forUser($request->user()->id)->find($id);
        $target = $notification?->link ?: route('admin.notifications.index');

        // فقط مسیرهای داخلی — جلوگیری از open-redirect
        if (! str_starts_with($target, '/') && ! str_starts_with($target, url('/'))) {
            $target = route('admin.notifications.index');
        }

        return redirect()->to($target);
    }

    /**
     * خوانده‌شده یک اعلان (AJAX)
     */
    public function markRead(int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($id, auth()->user());

        return response()->json(['success' => $success]);
    }

    /**
     * خوانده‌شده همه اعلان‌ها (AJAX)
     */
    public function markAllRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(auth()->user());

        return response()->json(['success' => true, 'count' => $count]);
    }
}
