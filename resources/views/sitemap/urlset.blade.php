{{--
    یک urlset کامل — همهٔ مقادیر با {{ }} اسکیپ می‌شوند تا کاراکترهایی مثل & در
    اسلاگ یا پارامتر query فایل XML را نشکند (نسخهٔ قبلی از route() بدون اسکیپ
    استفاده می‌کرد).
--}}
{!! '<' . '?' . 'xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($entries as $entry)
    <url>
        <loc>{{ url($entry['path']) }}</loc>
@if ($entry['lastmod'])
        <lastmod>{{ $entry['lastmod'] }}</lastmod>
@endif
@if ($entry['changefreq'])
        <changefreq>{{ $entry['changefreq'] }}</changefreq>
@endif
@if ($entry['priority'] !== null)
        <priority>{{ number_format((float) $entry['priority'], 1, '.', '') }}</priority>
@endif
@foreach ($entry['alternates'] as $locale => $path)
        <xhtml:link rel="alternate" hreflang="{{ $locale }}" href="{{ url($path) }}"/>
@endforeach
    </url>
@endforeach
</urlset>
