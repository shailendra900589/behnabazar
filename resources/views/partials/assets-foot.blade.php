{{-- Core JS: jQuery + Bootstrap + plugins + app (no Vite/npm) --}}
@php $bbJsVer = @filemtime(public_path('js/app.js')) ?: '1'; @endphp
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/app.js') }}?v={{ $bbJsVer }}"></script>
