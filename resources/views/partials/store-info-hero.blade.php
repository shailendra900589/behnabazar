@props([
    'title',
    'subtitle',
    'icon' => 'bi-info-circle-fill',
    'gradient' => 'linear-gradient(135deg, var(--bb-ink) 0%, #1e3a5f 100%)',
])
<div class="bb-card rounded-4 p-4 p-lg-5 mb-5 text-white position-relative overflow-hidden" style="background: {{ $gradient }};">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="row align-items-center position-relative z-1">
        <div class="col-md-8 text-center text-md-start mb-3 mb-md-0">
            <h1 class="display-6 fw-bold mb-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2">
                {{ $title }}
                <i class="bi {{ $icon }} text-warning"></i>
            </h1>
            <p class="mb-0 text-white-50 lead" style="max-width: 640px;">{{ $subtitle }}</p>
        </div>
        <div class="col-md-4 text-center text-md-end d-none d-md-block">
            <i class="bi {{ $icon }} text-white opacity-25" style="font-size: 7rem;"></i>
        </div>
    </div>
</div>
