{{-- Self-hosted assets (no Vite/npm, no CDN) — works on all live servers --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/nprogress/nprogress.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/app.css') }}?v=2" rel="stylesheet">
<style>
    #nprogress .bar { background: var(--bb-primary-dark) !important; height: 3px !important; }
    #nprogress .peg { box-shadow: 0 0 10px var(--bb-primary-dark), 0 0 5px var(--bb-primary-dark) !important; }
    #nprogress .spinner-icon { border-top-color: var(--bb-primary-dark) !important; border-left-color: var(--bb-primary-dark) !important; }
</style>
