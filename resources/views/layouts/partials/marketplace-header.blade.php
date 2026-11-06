@php
    $overHero = $overHero ?? false;
@endphp
<header
    class="da-marketplace-nav @if($overHero) da-marketplace-nav--over-hero @else da-marketplace-nav--solid @endif"
    id="daMarketplaceNav"
>
    <div class="container">
        <div class="da-marketplace-nav__inner">
            <a href="{{ route('home') }}" class="da-marketplace-brand text-decoration-none">
                <div class="da-marketplace-brand__lockup">
                    <div class="da-marketplace-brand__row">
                        <x-brand.dormai-logo
                            variant="mark"
                            surface="light"
                            class="da-marketplace-logo da-marketplace-logo--mark"
                        />
                        <x-brand.dormai-logo
                            variant="wordmark"
                            surface="light"
                            class="da-marketplace-logo da-marketplace-logo--wordmark"
                            :brand-primary="$marketplaceListingContent['brand_title_primary']"
                            :brand-accent="$marketplaceListingContent['brand_title_accent']"
                            wordmark-crop
                        />
                    </div>
                    @if(filled($marketplaceListingContent['brand_tagline'] ?? null))
                        <span class="da-marketplace-logo-tagline">{{ $marketplaceListingContent['brand_tagline'] }}</span>
                    @endif
                </div>
            </a>

            <nav class="da-marketplace-nav__menu d-none d-xl-flex" aria-label="Primary">
                <a href="{{ route('home') }}" class="da-marketplace-nav-link @if(request()->routeIs('home') && !request()->hasAny(['q', 'city', 'min_rate', 'max_rate'])) is-active @endif">Home</a>
                <a href="{{ route('home') }}#properties" class="da-marketplace-nav-link @if(request()->routeIs('public.listings.*') || (request()->routeIs('home') && request()->hasAny(['q', 'city', 'min_rate', 'max_rate']))) is-active @endif">Properties</a>
                <a href="{{ route('public.contact.create') }}" class="da-marketplace-nav-link @if(request()->routeIs('public.contact.*')) is-active @endif">Contact</a>
                <a href="{{ route('register') }}" class="da-marketplace-nav-link">For owners</a>
            </nav>

            <div class="da-marketplace-nav__actions">
                @if(request()->routeIs('home'))
                    <button
                        type="button"
                        class="btn btn-sm da-marketplace-nav-search d-lg-none"
                        data-bs-toggle="modal"
                        data-bs-target="#daMarketplaceSearchModal"
                        aria-label="Search listings"
                    >
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <span class="da-marketplace-nav-search__label">Search</span>
                    </button>
                @endif
                <a href="{{ route('home') }}#hero-search" class="btn btn-sm da-marketplace-nav-chip d-none d-lg-inline-flex">
                    <i class="bi bi-search-heart" aria-hidden="true"></i>
                    <span>Quick search</span>
                </a>
                <button
                    type="button"
                    class="btn btn-sm da-marketplace-theme-btn da-marketplace-theme-btn--icon"
                    id="daMarketplaceThemeToggle"
                    data-da-theme-toggle
                    aria-pressed="false"
                    aria-label="Toggle dark mode"
                    title="Toggle dark mode"
                >
                    <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-sm da-marketplace-btn-register">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="da-marketplace-nav-login d-none d-md-inline">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-sm da-marketplace-btn-register">
                        <span class="d-none d-sm-inline">Register</span>
                        <span class="d-sm-none">Join</span>
                    </a>
                @endauth
                <button
                    type="button"
                    class="btn btn-sm da-marketplace-nav-toggle d-xl-none"
                    data-bs-toggle="collapse"
                    data-bs-target="#daMarketplaceNavCollapse"
                    aria-expanded="false"
                    aria-controls="daMarketplaceNavCollapse"
                    aria-label="Open menu"
                >
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

        <div class="collapse d-xl-none da-marketplace-nav__collapse" id="daMarketplaceNavCollapse">
            <nav class="da-marketplace-nav__menu da-marketplace-nav__menu--mobile pb-3" aria-label="Mobile primary">
                <a href="{{ route('home') }}" class="da-marketplace-nav-link">Home</a>
                <a href="{{ route('home') }}#properties" class="da-marketplace-nav-link">Properties</a>
                <a href="{{ route('public.contact.create') }}" class="da-marketplace-nav-link">Contact</a>
                <a href="{{ route('register') }}" class="da-marketplace-nav-link">For owners</a>
                @guest
                    <a href="{{ route('login') }}" class="da-marketplace-nav-link">Login</a>
                @endguest
            </nav>
        </div>
    </div>
</header>
