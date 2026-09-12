{{--
    تگ‌های SEO — تنها جایی که head مربوط به SEO تولید می‌شود.

    داده از `SeoResolver` می‌آید (زنجیرهٔ fallback در app/Services/Seo/SeoResolver.php).
    همهٔ کلیدها همیشه وجود دارند (مقدار null/خالی برای «تگ نزن»)، پس نیازی به
    `??` در این ویو نیست.
--}}

<title>{{ $seo['title'] }}</title>

@if ($seo['description'] !== '')
    <meta name="description" content="{{ $seo['description'] }}">
@endif

@if (! empty($seo['keywords']))
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif

{{-- دستورالعمل ربات‌ها — همیشه صریح، تا هیچ صفحه‌ای ناخواسته ایندکس نشود --}}
<meta name="robots" content="{{ $seo['robots'] }}">

{{-- canonical: همیشه self-referencing برای زبان جاری --}}
<link rel="canonical" href="{{ $seo['canonical'] }}">

{{-- hreflang: هر زبان فعال + x-default به زبان پیش‌فرض --}}
@foreach ($seo['alternates'] as $code => $url)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ $url }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $seo['x_default'] }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo['og']['type'] }}">
<meta property="og:url" content="{{ $seo['og']['url'] }}">
<meta property="og:title" content="{{ $seo['og']['title'] }}">
@if ($seo['og']['description'] !== '')
    <meta property="og:description" content="{{ $seo['og']['description'] }}">
@endif
@if ($seo['og']['image'])
    <meta property="og:image" content="{{ $seo['og']['image'] }}">
@endif
@if ($seo['og']['site_name'])
    <meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
@endif
<meta property="og:locale" content="{{ $seo['og']['locale'] }}">
@foreach ($seo['og']['locale_alternates'] as $alternateLocale)
    <meta property="og:locale:alternate" content="{{ $alternateLocale }}">
@endforeach

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $seo['twitter']['card'] }}">
@if ($seo['twitter']['site'])
    <meta name="twitter:site" content="{{ $seo['twitter']['site'] }}">
@endif
@if ($seo['twitter']['creator'])
    <meta name="twitter:creator" content="{{ $seo['twitter']['creator'] }}">
@endif
<meta name="twitter:title" content="{{ $seo['twitter']['title'] }}">
@if ($seo['twitter']['description'] !== '')
    <meta name="twitter:description" content="{{ $seo['twitter']['description'] }}">
@endif
@if ($seo['twitter']['image'])
    <meta name="twitter:image" content="{{ $seo['twitter']['image'] }}">
@endif

{{-- دادهٔ ساختاریافته (JSON-LD) — یک بلوک @graph برای کل صفحه.
     همیشه با json_encode ساخته می‌شود؛ هیچ مقداری مستقیم داخل JSON نوشته نمی‌شود.
     JSON_HEX_TAG/AMP/APOS/QUOT مانع از شکستن اسکریپت با دادهٔ کاربر (مثل »</script>« در نام محصول) می‌شود. --}}
@if (! empty($graph))
    <script type="application/ld+json">{!! json_encode(
        $graph,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) !!}</script>
@endif
