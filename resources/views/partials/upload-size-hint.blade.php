@php
    $guide = \App\Support\UploadImageGuide::get($type ?? '');
@endphp
@if($guide)
    <div class="form-text bb-upload-size-hint">
        <strong>Image size:</strong> {{ $guide['size'] }} <span class="text-muted">({{ $guide['ratio'] }})</span><br>
        {{ $guide['format'] }} · max {{ $guide['max'] }}
        @if(!empty($guide['tip']))
            <br><span class="text-muted">{{ $guide['tip'] }}</span>
        @endif
    </div>
@endif
