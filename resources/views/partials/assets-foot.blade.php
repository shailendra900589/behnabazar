@php
    $bbJquery = \App\Support\BbAsset::url('vendor/jquery/jquery.min.js');
    $bbNprogress = \App\Support\BbAsset::url('vendor/nprogress/nprogress.min.js');
    $bbSwal = \App\Support\BbAsset::url('vendor/sweetalert2/sweetalert2.all.min.js');
    $bbBootstrapJs = \App\Support\BbAsset::url('vendor/bootstrap/js/bootstrap.bundle.min.js');
    $bbThemeJs = \App\Support\BbAsset::url('js/app.js');
@endphp
<script src="{{ $bbJquery }}"></script>
<script src="{{ $bbNprogress }}"></script>
<script src="{{ $bbSwal }}"></script>
<script src="{{ $bbBootstrapJs }}"></script>
<script src="{{ $bbThemeJs }}"></script>
