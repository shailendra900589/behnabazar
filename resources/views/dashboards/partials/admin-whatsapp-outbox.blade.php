@php
    $biz = preg_replace('/\D/', '', $settings['whatsapp_business_phone'] ?? '');
    $pendingList = $whatsappOutbox ?? collect();
@endphp
<div class="admin-section" id="tab-whatsapp">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1"><i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Outbox (apna jugad)</h2>
            <p class="text-muted small mb-0">
                @if(app(\App\Services\WhatsApp\WhatsAppCloudSender::class)->isConfigured())
                    <span class="text-success fw-semibold">Auto mode ON</span> — pending messages cron se khud bheji jati hain (har minute).
                    Yahan sirf failed/manual backup dikhega.
                @else
                    Manual mode — WhatsApp Web apne <strong>Business number</strong> se login karke <em>Send</em> dabao.
                    Poora auto ke liye Program settings mein Meta Cloud API token daalo.
                @endif
            </p>
        </div>
        @if($pendingList->count() > 0)
            <form method="post" action="{{ route('manage.whatsapp-outbox.skip-all') }}" onsubmit="return confirm('Skip all pending messages?');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Clear all pending</button>
            </form>
        @endif
    </div>

    @if($biz === '')
        <div class="alert alert-warning">
            Pehle <a href="{{ route('dashboard', ['section' => 'program']) }}">Program settings</a> mein <strong>Business WhatsApp number</strong> daalo.
        </div>
    @else
        <p class="small mb-3">
            Business number: <strong>{{ $biz }}</strong> —
            <a href="https://web.whatsapp.com/" target="_blank" rel="noopener">WhatsApp Web kholo</a>
            (isi number se login)
        </p>
    @endif

    @if($pendingList->isEmpty())
        <div class="bb-card p-5 text-center text-muted">
            <i class="bi bi-check-circle text-success display-6 d-block mb-2"></i>
            Koi pending WhatsApp message nahi. Naya order aane par yahan dikhega.
        </div>
    @else
        <div class="mb-3">
            <button type="button" class="btn btn-success" id="bbWaSendNext">
                <i class="bi bi-whatsapp me-1"></i> Send next ({{ $pendingList->count() }})
            </button>
            <span class="small text-muted ms-2">Har click = ek chat khulegi, message typed — bas Send dabana</span>
        </div>
        <div class="table-responsive bb-card">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>To</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingList as $row)
                        <tr data-outbox-id="{{ $row->id }}">
                            <td class="text-nowrap">
                                <strong>{{ $row->displayPhone() }}</strong>
                                @if($row->recipient_label)
                                    <br><span class="small text-muted">{{ $row->recipient_label }}</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $row->template }}</span></td>
                            <td class="small" style="max-width:320px">{{ \Illuminate\Support\Str::limit($row->message, 120) }}</td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-success bb-wa-send-one" data-url="{{ $row->wa_url }}" data-id="{{ $row->id }}">
                                    <i class="bi bi-whatsapp"></i> Send
                                </button>
                                <form method="post" action="{{ route('manage.whatsapp-outbox.skip', $row) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Skip</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(($whatsappSent ?? collect())->isNotEmpty())
        <h3 class="h6 fw-bold mt-4 mb-2">Recently sent</h3>
        <div class="small text-muted">
            @foreach($whatsappSent as $row)
                <div class="py-1 border-bottom">{{ $row->displayPhone() }} — {{ $row->template }} — {{ $row->sent_at?->diffForHumans() }}</div>
            @endforeach
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function openAndMark(url, id) {
        window.open(url, '_blank', 'noopener');
        if (!id || !csrf) return;
        fetch('{{ url('/manage/whatsapp-outbox') }}/' + id + '/sent', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        }).then(r => r.json()).then(data => {
            const row = document.querySelector('[data-outbox-id="' + id + '"]');
            if (row) row.remove();
            const badge = document.getElementById('bbWaPendingBadge');
            if (badge && data.pending !== undefined) {
                if (data.pending > 0) badge.textContent = data.pending;
                else badge.remove();
            }
        }).catch(() => {});
    }

    document.querySelectorAll('.bb-wa-send-one').forEach(btn => {
        btn.addEventListener('click', () => openAndMark(btn.dataset.url, btn.dataset.id));
    });

    const nextBtn = document.getElementById('bbWaSendNext');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const first = document.querySelector('.bb-wa-send-one');
            if (first) openAndMark(first.dataset.url, first.dataset.id);
        });
    }
});
</script>
