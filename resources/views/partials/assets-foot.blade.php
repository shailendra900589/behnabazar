@php
    $bbJs = \App\Support\BbAsset::url('js/behnabazar.min.js');
    $bbJquery = \App\Support\BbAsset::url('vendor/jquery/jquery.min.js');
    $bbSwal = \App\Support\BbAsset::url('vendor/sweetalert2/sweetalert2.all.min.js');
    $bbExtras = \App\Support\BbAsset::url('js/site-extras.js');
@endphp
<script src="{{ $bbJquery }}"></script>
<script src="{{ $bbSwal }}"></script>
<script src="{{ $bbJs }}?v=3"></script>
<script src="{{ $bbExtras }}?v=3"></script>
