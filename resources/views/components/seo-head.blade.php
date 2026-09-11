<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
@if($seo->keywords)
<meta name="keywords" content="{{ $seo->keywords }}">
@endif
<link rel="canonical" href="{{ $seo->canonical_url }}">
<meta name="robots" content="{{ $seo->robots }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $seo->og_type }}">
<meta property="og:url" content="{{ $seo->og_url }}">
<meta property="og:title" content="{{ $seo->og_title }}">
<meta property="og:description" content="{{ $seo->og_description }}">
@if($seo->og_image)
<meta property="og:image" content="{{ $seo->og_image }}">
@endif
@if($seo->og_site_name)
<meta property="og:site_name" content="{{ $seo->og_site_name }}">
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $seo->twitter_card }}">
@if($seo->twitter_site)
<meta name="twitter:site" content="{{ $seo->twitter_site }}">
@endif
@if($seo->twitter_creator)
<meta name="twitter:creator" content="{{ $seo->twitter_creator }}">
@endif
<meta name="twitter:title" content="{{ $seo->twitter_title }}">
<meta name="twitter:description" content="{{ $seo->twitter_description }}">
@if($seo->twitter_image)
<meta name="twitter:image" content="{{ $seo->twitter_image }}">
@endif
