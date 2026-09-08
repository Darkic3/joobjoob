<?php

namespace App\View\Composers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'unreadNotificationCount' => Auth::check()
                ? $this->notificationService->getAdminUnreadCount(Auth::user())
                : 0,
        ]);
    }
}
