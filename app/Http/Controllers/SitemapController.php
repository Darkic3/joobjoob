<?php

namespace App\Http\Controllers;

use App\Services\Seo\SitemapBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * sitemap.xml به‌صورت ایندکس + فایل‌های فرزند به‌ازای هر نوع محتوا.
 *
 * چرا ایندکس و نه یک فایل غول: هر نوع محتوا جدا کش می‌شود (تغییر یک محصول
 * نباید بازسازی کل sitemap را لازم کند)، لاگ‌های سرچ کنسول نشان می‌دهند کدام
 * نوع محتوا مشکل دارد، و سقف ۵۰٬۰۰۰ URL گوگل خودکار رعایت می‌شود.
 */
class SitemapController extends Controller
{
    public function __construct(private readonly SitemapBuilder $builder) {}

    /** sitemap index — فقط فهرست فایل‌های فرزند */
    public function index(): Response
    {
        return $this->xml('sitemap.index', [
            'children' => $this->builder->index(),
        ]);
    }

    /** فایل فرزند یک نوع (صفحهٔ اول) */
    public function show(Request $request): Response
    {
        return $this->urlset((string) $request->route('type'), 1);
    }

    /** فایل فرزند یک نوع، صفحه‌های بعدی (وقتی تعداد URL از سقف بیشتر است) */
    public function chunk(Request $request): Response
    {
        return $this->urlset((string) $request->route('type'), (int) $request->route('page'));
    }

    private function urlset(string $type, int $page): Response
    {
        $entries = $this->builder->entries($type, $page);

        abort_if($entries === [], 404);

        return $this->xml('sitemap.urlset', compact('entries'));
    }

    /**
     * ⚠️ نوع محتوا `application/xml` است و نه `text/xml`:
     * میدل‌ور LocalizeHtmlDigits هر پاسخ `text/*` را HTML فرض می‌کند و ارقامش را
     * بومی‌سازی می‌کند — یعنی تاریخ‌های sitemap به ارقام فارسی تبدیل می‌شدند و
     * فایل برای خزنده‌ها نامعتبر. با `application/xml` این مسیر کاملاً رد می‌شود.
     */
    private function xml(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
