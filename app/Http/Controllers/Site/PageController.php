<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\NewsletterSubscribeRequest;
use App\Http\Requests\Site\SendContactRequest;
use App\Models\ContactRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    /** صفحات استاتیک معتبر — متن آن‌ها از فایل‌های ترجمه (messages.pages.*) خوانده می‌شود */
    public const PAGES = ['contact', 'about', 'faq', 'terms', 'privacy'];

    public function show(string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);

        /** @var array<string, mixed> $content */
        $content = (array) trans("messages.pages.{$page}");

        $title = (string) ($content['title'] ?? ucfirst($page));
        $description = (string) ($content['description'] ?? '');

        $data = $this->pageData($page, $content);

        // SEO: صفحات استاتیک محتوایشان از فایل ترجمه می‌آید ⇒ عنوان/توضیح صریح +
        // مسیر ثابت برای امکان ویرایش متا از پنل (ردیف seo_meta با path = "/page/{page}").
        seo()
            ->forPath('/page/'.$page)
            ->title($title)
            ->description($description !== '' ? $description : null);

        return view('site.pages.show', compact('page', 'title', 'description', 'data'));
    }

    /**
     * دریافت پیام از فرم تماس — با throttle (۵ ارسال در دقیقه) در روت محافظت می‌شود.
     */
    public function sendContact(SendContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ContactRequest::create($validated);

        // ── Notification: new contact message ──
        try {
            app(NotificationService::class)->notifyAdmins(
                'contact.new',
                'پیام تماس جدید',
                null,
                route('admin.contact.index'),
                ['name' => $validated['name'], 'email' => $validated['email']]
            );
        } catch (\Throwable $e) { Log::error('Contact notification failed', ['message' => $e->getMessage()]); }

        return back()->with('success', __('messages.pages.contact.success'));
    }

    /**
     * عضویت در خبرنامه — AJAX.
     *
     * ایمیل در جدول newsletter_subscribers ذخیره می‌شود؛
     * اگر خبرنامه از تنظیمات ادمین غیرفعال باشد، درخواست رد می‌شود.
     */
    public function subscribeNewsletter(NewsletterSubscribeRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        $service = app(\App\Services\NewsletterService::class);
        $result = $service->subscribe($email, auth()->id());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['message'],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
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
