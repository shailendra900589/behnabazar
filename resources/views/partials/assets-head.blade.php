{{-- Core CSS: Bootstrap + Icons + custom theme (no Vite/npm) --}}
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css" rel="stylesheet">
@php $bbCssVer = @filemtime(public_path('css/app.css')) ?: '1'; @endphp
<link href="{{ asset('css/app.css') }}?v={{ $bbCssVer }}" rel="stylesheet">
<style>
    #nprogress .bar { background: var(--bb-primary-dark) !important; height: 3px !important; }
    #nprogress .peg { box-shadow: 0 0 10px var(--bb-primary-dark), 0 0 5px var(--bb-primary-dark) !important; }
    #nprogress .spinner-icon { border-top-color: var(--bb-primary-dark) !important; border-left-color: var(--bb-primary-dark) !important; }
</style>
