<div class="admin-section" id="tab-notifications">
    <h2 class="h5 fw-bold mb-3">SMS &amp; WhatsApp log</h2>
    <p class="text-muted small mb-3">Last 100 notification attempts (sent, queued, failed).</p>
    <div class="table-responsive bb-card">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Channel</th>
                    <th>To</th>
                    <th>Template</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notificationLogs ?? [] as $log)
                    <tr>
                        <td class="text-nowrap small">{{ $log->created_at->format('d M H:i') }}</td>
                        <td><span class="badge bg-light text-dark">{{ $log->channel }}</span></td>
                        <td class="small">{{ $log->recipient }}</td>
                        <td class="small text-muted">{{ $log->template }}</td>
                        <td>
                            @php
                                $badge = match($log->status) {
                                    'sent' => 'success',
                                    'queued' => 'warning',
                                    'failed' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $log->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-4">No logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
