@php
    $vendorNotifications = $vendorNotifications ?? collect();
    $vendorNotificationCount = $vendorNotificationUnread ?? 0;
@endphp
@if(auth()->check() && auth()->user()->role === 'vendor')
<div class="dropdown vendor-notify-dropdown">
    <button class="btn btn-soft btn-sm rounded-pill position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Vendor notifications">
        <i class="bi bi-bell"></i>
        @if($vendorNotificationCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">{{ $vendorNotificationCount > 9 ? '9+' : $vendorNotificationCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" style="width: min(360px, 92vw);">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <span class="fw-bold small">Resell &amp; shop alerts</span>
            @if($vendorNotificationCount > 0)
                <form method="post" action="{{ route('manage.notifications.read-all') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="vendor-notify-list" style="max-height: 320px; overflow-y: auto;">
            @forelse($vendorNotifications as $n)
                <div class="vendor-notify-item px-3 py-2 border-bottom {{ $n->read_at ? '' : 'vendor-notify-unread' }}">
                    <div class="fw-semibold small">{{ $n->title }}</div>
                    @if($n->body)
                        <p class="small text-muted mb-1">{{ $n->body }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted" style="font-size: 0.7rem;">{{ $n->created_at->diffForHumans() }}</span>
                        <div class="d-flex gap-1">
                            @if($n->link)
                                <a href="{{ $n->link }}" class="btn btn-outline-dark btn-sm py-0 px-2" style="font-size: 0.7rem;">View</a>
                            @endif
                            @if(! $n->read_at)
                                <form method="post" action="{{ route('manage.notifications.read', $n) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-soft btn-sm py-0 px-2" style="font-size: 0.7rem;">Read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted small text-center py-4 mb-0">No notifications yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endif
