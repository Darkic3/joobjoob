<?php

namespace App\Services\Seo;

/**
 * ساخت محتوای `/robots.txt` از تنظیمات ادمین و fallback کانفیگ.
 *
 * چرا داینامیک و نه فایل استاتیک: تنها راهی که خط `Sitemap:` و قواعد `Disallow`
 * از پنل ادمین قابل مدیریت باشند و دامنه از درخواست واقعی بیاید. نسخهٔ استاتیک
 * `public/robots.txt` دامنهٔ هاردکد `https://example.com` را به گوگل اعلام می‌کرد
 * و به `/profile` و `/register` اشاره می‌کرد که در این پروژه وجود ندارند.
 *
 * قرارداد: اگر ادمین متن آزاد ذخیره کرده باشد، همان متن مبنا است و فقط مطمئن
 * می‌شویم خط `Sitemap:` در آن هست.
 */
class RobotsBuilder
{
    public function content(): string
    {
        $custom = trim((string) setting('seo_robots_txt'));

        $body = $custom !== '' ? $custom : $this->defaultBody();

        return $this->withSitemapLine($body);
    }

    /** مسیر sitemap ایندکس روی همین دامنه */
    public function sitemapUrl(): string
    {
        return url(route('sitemap', [], false));
    }

    // ==========================================
    // بدنهٔ پیش‌فرض
    // ==========================================

    private function defaultBody(): string
    {
        $lines = ['User-agent: *', 'Allow: /'];

        $disallowed = (array) setting('seo_robots_disallow', config('seo.disallow_paths', []));

        $lines[] = '';
        $lines[] = '# مسیرهای مسدود — از پنل مدیریت قابل ویرایش است';

        foreach ($disallowed as $path) {
            $path = trim((string) $path);

            if ($path === '') {
                continue;
            }

            $lines[] = 'Disallow: '.$this->normalizePath($path);
        }

        return implode("\n", $lines);
    }

    /**
     * `host/path` → `/path` و تضمین اسلش ابتدایی.
     * والگارد: یک خط اشتباه در پنل نباید کل robots.txt را بی‌معنا کند.
     */
    private function normalizePath(string $path): string
    {
        if (preg_match('~^https?://~i', $path)) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        return $path === '' ? '/' : '/'.ltrim($path, '/');
    }

    // ==========================================
    // خط Sitemap
    // ==========================================

    private function withSitemapLine(string $body): string
    {
        $sitemap = $this->sitemapUrl();

        // اگر ادمین خط Sitemap را خودش نوشته، دست نمی‌زنیم
        if (preg_match('~^Sitemap:~mi', $body)) {
            return rtrim($body)."\n";
        }

        return rtrim($body)."\n\nSitemap: ".$sitemap."\n";
    }
}
