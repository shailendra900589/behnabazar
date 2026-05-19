@php
    $filterQs = $filterQs ?? [];
@endphp
<div class="bb-category-rail mb-4">
    <div class="bb-category-rail-track" role="tablist" aria-label="Product categories">
        <a class="bb-category-chip {{ ! request('cat') ? 'is-active' : '' }}"
           href="{{ route('home', $filterQs) }}"
           role="tab"
           aria-selected="{{ ! request('cat') ? 'true' : 'false' }}">
            All
        </a>
        @foreach ($categories as $cat)
            <a class="bb-category-chip {{ request('cat') === $cat->slug ? 'is-active' : '' }}"
               href="{{ route('home', array_merge($filterQs, ['cat' => $cat->slug])) }}"
               role="tab"
               aria-selected="{{ request('cat') === $cat->slug ? 'true' : 'false' }}">
                <i class="bi {{ $cat->icon }}" aria-hidden="true"></i>
                <span>{{ $cat->name }}</span>
            </a>
        @endforeach
    </div>
</div>