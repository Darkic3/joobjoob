<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                'data' => $notifications->getCollection()->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'icon' => $n->icon,
                    'link' => $n->link,
                    'is_read' => (bool) $n->is_read,
                    'created_at' => $n->created_at?->diffForHumans(),
                ])->values(),
                'count' => $this->notificationService->getAdminUnreadCount(),
            ]);
        }

        return view('admin.notifications.index', compact('notifications', 'filter'));
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
