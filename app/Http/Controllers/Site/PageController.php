<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /** صفحات استاتیک معتبر — متن آن‌ها از فایل‌های ترجمه (messages.pages.*) خوانده می‌شود */
    private const PAGES = ['contact', 'about', 'faq', 'terms', 'privacy'];

    public function show(string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);

        /** @var array<string, mixed> $content */
        $content = (array) trans("messages.pages.{$page}");

        $title = (string) ($content['title'] ?? ucfirst($page));
        $description = (string) ($content['description'] ?? '');

        $data = $this->pageData($page, $content);

        return view('site.pages.show', compact('page', 'title', 'description', 'data'));
    }

    /**
     * دریافت پیام از فرم تماس — با throttle (۵ ارسال در دقیقه) در روت محافظت می‌شود.
     */
    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'department' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        ContactRequest::create($validated);

        // ── Notification: new contact message ──
        try {
            app(NotificationService::class)->notifyAdmins(
                'contact.new',
                'پیام تماس جدید',
                "پیام جدید از {$validated['name']} ({$validated['email']}) دریافت شد.",
                route('admin.contact.index'),
                ['name' => $validated['name'], 'email' => $validated['email']]
            );
        } catch (\Throwable $e) { Log::error('Contact notification failed', ['message' => $e->getMessage()]); }

        return back()->with('success', __('messages.pages.contact.success'));
    }

    /**
     * داده‌های اختصاصی هر صفحه — اطلاعات تماس از تنظیمات سایت خوانده می‌شود.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function pageData(string $page, array $content): array
    {
        return match ($page) {
            'contact' => array_merge($content, [
                'info' => [
                    ['icon' => 'phone', 'label' => __('messages.pages.contact.info_phone'), 'value' => setting('phone'), 'dir' => 'ltr'],
                    ['icon' => 'email', 'label' => __('messages.pages.contact.info_email'), 'value' => setting('email'), 'dir' => 'ltr'],
                    ['icon' => 'pin', 'label' => __('messages.pages.contact.info_address'), 'value' => setting('address_one'), 'dir' => null],
                ],
                'departments' => $content['departments'] ?? [],
            ]),
            default => $content,
        };
    }
}
