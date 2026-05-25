@php
    $bbJquery = \App\Support\BbAsset::url('vendor/jquery/jquery.min.js');
    $bbNprogress = \App\Support\BbAsset::url('vendor/nprogress/nprogress.min.js');
    $bbBootstrapJs = \App\Support\BbAsset::url('vendor/bootstrap/js/bootstrap.bundle.min.js');
    $bbThemeJs = \App\Support\BbAsset::url('js/app.js');
    $bbShareJs = \App\Support\BbAsset::url('js/share.js');
    $bbGalleryJs = \App\Support\BbAsset::url('js/product-gallery.js');
@endphp
<script src="{{ $bbJquery }}"></script>
<script src="{{ $bbBootstrapJs }}"></script>
<script src="{{ $bbNprogress }}"></script>
<script src="{{ $bbThemeJs }}"></script>
<script src="{{ $bbShareJs }}" defer></script>
<script src="{{ $bbGalleryJs }}" defer></script>
