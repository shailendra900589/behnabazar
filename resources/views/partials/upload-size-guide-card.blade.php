<div class="bb-card p-3 p-md-4 mb-4 bb-upload-guide-card">
    <h5 class="fw-bold mb-2"><i class="bi bi-aspect-ratio me-1 text-bloom"></i> Image size guide</h5>
    <p class="small text-muted mb-3 mb-md-2">Use these sizes for sharp display on mobile and desktop. JPG/WebP under 2 MB load fastest.</p>
    <div class="table-responsive">
        <table class="table table-sm table-borderless mb-0 small bb-upload-guide-table">
            <thead class="text-muted">
                <tr>
                    <th>Upload type</th>
                    <th>Size</th>
                    <th>Ratio</th>
                </tr>
            </thead>
            <tbody>
                @foreach (config('upload-images', []) as $key => $row)
                    <tr>
                        <td class="fw-semibold">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                        <td>{{ $row['size'] }}</td>
                        <td class="text-muted">{{ $row['ratio'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
