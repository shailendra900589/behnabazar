<div class="bb-mobile-search d-lg-none">
    <form class="bb-mobile-search-form" action="{{ route('home') }}" method="get" role="search">
        @if (request('cat'))
            <input type="hidden" name="cat" value="{{ request('cat') }}">
        @endif
        <span class="bb-mobile-search-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
        <input
            type="search"
            name="search"
            class="bb-mobile-search-input"
            value="{{ request('search') }}"
            placeholder="Search products, brands..."
            autocomplete="off"
            enterkeyhint="search"
        >
        @if (request('search'))
            <a href="{{ route('home', array_filter(['cat' => request('cat')])) }}" class="bb-mobile-search-clear" aria-label="Clear search">
                <i class="bi bi-x-lg"></i>
            </a>
        @endif
    </form>
</div>
