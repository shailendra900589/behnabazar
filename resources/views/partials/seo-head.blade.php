@php
    $seo = $seo ?? null;
    $seoCfg = \App\Support\Seo\SiteSeoSettings::config();
    $googleVerify = \App\Support\Seo\SearchEngineIndexer::googleVerification();
    $bingVerify = \App\Support\Seo\SearchEngineIndexer::bingVerification();
    $indexNowKey = \App\Support\Seo\SearchEngineIndexer::indexNowKey();
@endphp
@if($googleVerify !== '')
    <meta name="google-site-verification" content="{{ $googleVerify }}">
@endif
@if($bingVerify !== '')
    <meta name="msvalidate.01" content="{{ $bingVerify }}">
@endif
@if($indexNowKey)
    <meta name="indexnow" content="{{ $indexNowKey }}">
@endif
<link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap', [], true) }}">
@if($seo instanceof \App\Support\Seo\SeoMeta)
    <title>{{ $seo->title }}</title>
    <meta name="description" content="{{ $seo->description }}">
    @if($seo->keywordsString() !== '')
        <meta name="keywords" content="{{ $seo->keywordsString() }}">
    @endif
    <meta name="robots" content="{{ $seo->robotsContent() }}">
    <link rel="canonical" href="{{ $seo->canonical }}">

    {{-- GEO (local / geographic SEO) --}}
    <meta name="geo.region" content="{{ $seoCfg['country'] }}-{{ $seoCfg['region'] }}">
    <meta name="geo.placename" content="{{ $seoCfg['locality'] }}">
    <meta name="geo.position" content="{{ $seoCfg['latitude'] }};{{ $seoCfg['longitude'] }}">
    <meta name="ICBM" content="{{ $seoCfg['latitude'] }}, {{ $seoCfg['longitude'] }}">
    <meta name="language" content="{{ $seoCfg['locale'] }}">

    {{-- Open Graph --}}
    <meta property="og:locale" content="{{ str_replace('-', '_', $seoCfg['locale']) }}">
    <meta property="og:type" content="{{ $seo->type === 'product' ? 'product' : 'website' }}">
    <meta property="og:site_name" content="{{ $seoCfg['site_name'] }}">
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:description" content="{{ $seo->description }}">
    <meta property="og:url" content="{{ $seo->canonical }}">
    @if($seo->image)
        <meta property="og:image" content="{{ $seo->image }}">
        <meta property="og:image:alt" content="{{ $seo->title }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->title }}">
    <meta name="twitter:description" content="{{ $seo->description }}">
    @if($seo->image)
        <meta name="twitter:image" content="{{ $seo->image }}">
    @endif

    {{-- AEO / SEO: JSON-LD structured data --}}
    @foreach($seo->jsonLd as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endforeach
@else
    <title>@yield('title', $siteBranding['name'] ?? config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', $siteBranding['name'] ?? config('app.name'))">
@endif
