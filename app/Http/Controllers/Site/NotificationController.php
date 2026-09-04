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
                'data' => collect($notifications->items())->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'icon' => $n->icon,
                    'link' => $n->link,
                    'is_read' => (bool) $n->is_read,
                    'created_at' => $n->created_at?->diffForHumans(),
                ])->values(),
                'count' => $this->notificationService->getUnreadCount(Auth::user()),
            ]);
        }

        return view('site.dashboard.notifications', compact('notifications'));
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
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return response()->json(['success' => true, 'count' => $count]);
    }
}
