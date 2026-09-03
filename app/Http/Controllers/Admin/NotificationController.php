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
