{{-- Static assets only (public/vendor + public/css). No npm. No Vite. --}}
@php
    $bbBootstrapCss = \App\Support\BbAsset::url('vendor/bootstrap/css/bootstrap.min.css');
    $bbIconsCss = \App\Support\BbAsset::url('vendor/bootstrap-icons/font/bootstrap-icons.min.css');
    $bbNprogressCss = \App\Support\BbAsset::url('vendor/nprogress/nprogress.css');
    $bbThemeCss = \App\Support\BbAsset::url('css/app.css');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://images.unsplash.com">
<link href="{{ $bbBootstrapCss }}" rel="stylesheet">
<link href="{{ $bbThemeCss }}" rel="stylesheet">
<link href="{{ $bbIconsCss }}" rel="stylesheet" media="print" onload="this.media='all'">
<link href="{{ $bbNprogressCss }}" rel="stylesheet">
<style>
#nprogress .bar{background:var(--bb-primary-dark,#4f46e5)!important;height:3px!important}
#nprogress .peg{box-shadow:0 0 10px var(--bb-primary-dark,#4f46e5),0 0 5px var(--bb-primary-dark,#4f46e5)!important}
</style>
