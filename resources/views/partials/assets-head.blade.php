{{-- Static assets only (public/vendor + public/css). No npm. No Vite. --}}
@php
    $bbBootstrapCss = \App\Support\BbAsset::url('vendor/bootstrap/css/bootstrap.min.css');
    $bbIconsCss = \App\Support\BbAsset::url('vendor/bootstrap-icons/font/bootstrap-icons.min.css');
    $bbSwalCss = \App\Support\BbAsset::url('vendor/sweetalert2/sweetalert2.min.css');
    $bbNprogressCss = \App\Support\BbAsset::url('vendor/nprogress/nprogress.css');
    $bbThemeCss = \App\Support\BbAsset::url('css/app.css');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{{ $bbNprogressCss }}" rel="stylesheet">
<link href="{{ $bbBootstrapCss }}" rel="stylesheet">
<link href="{{ $bbSwalCss }}" rel="stylesheet">
<link href="{{ $bbIconsCss }}" rel="stylesheet">
<link href="{{ $bbThemeCss }}" rel="stylesheet">
<style>
    #nprogress .bar { background: var(--bb-primary-dark, #4f46e5) !important; height: 3px !important; }
    #nprogress .peg { box-shadow: 0 0 10px var(--bb-primary-dark, #4f46e5), 0 0 5px var(--bb-primary-dark, #4f46e5) !important; }
    #nprogress .spinner-icon { border-top-color: var(--bb-primary-dark, #4f46e5) !important; border-left-color: var(--bb-primary-dark, #4f46e5) !important; }
</style>
