<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    /** صفحات استاتیک معتبر — متن آن‌ها از فایل‌های ترجمه (messages.pages.*) خوانده می‌شود */
    private const PAGES = ['contact', 'about', 'faq', 'terms', 'privacy'];

    public function show(string $page)
    {
        abort_unless(in_array($page, self::PAGES, true), 404);

        $title = (string) __("messages.pages.{$page}.title");
        $description = (string) __("messages.pages.{$page}.description");

        return view('site.pages.show', compact('page', 'title', 'description'));
    }
}
