{{-- Single bundled CSS (Bootstrap + Icons + theme) — same as former Vite build --}}
@php
    $bbCss = \App\Support\BbAsset::url('css/behnabazar.min.css');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{{ $bbCss }}?v=3" rel="stylesheet">
<style>
    #nprogress .bar { background: var(--bb-primary-dark, #4f46e5) !important; height: 3px !important; }
    #nprogress .peg { box-shadow: 0 0 10px var(--bb-primary-dark, #4f46e5), 0 0 5px var(--bb-primary-dark, #4f46e5) !important; }
    #nprogress .spinner-icon { border-top-color: var(--bb-primary-dark, #4f46e5) !important; border-left-color: var(--bb-primary-dark, #4f46e5) !important; }
</style>
