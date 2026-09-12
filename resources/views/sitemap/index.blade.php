{!! '<' . '?' . 'xml version="1.0" encoding="UTF-8"?' . '>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($children as $child)
    <sitemap>
        <loc>{{ $child['loc'] }}</loc>
@if ($child['lastmod'])
        <lastmod>{{ $child['lastmod'] }}</lastmod>
@endif
    </sitemap>
@endforeach
</sitemapindex>
