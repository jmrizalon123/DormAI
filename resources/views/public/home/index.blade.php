@extends('layouts.marketplace')

@section('title', config('app.name').' — Dormitories & boarding houses for rent')

@section('hero')
    <section class="da-marketplace-hero">
        <div class="da-marketplace-hero__slideshow" @if(count($heroImageUrls) > 1) data-da-hero-slideshow @endif>
            @foreach($heroImageUrls as $index => $slideUrl)
                <img
                    class="da-marketplace-hero__photo @if($index === 0) is-active @endif"
                    src="{{ $slideUrl }}"
                    alt=""
                    width="1920"
                    height="1080"
                    decoding="async"
                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    @if($index === 0) fetchpriority="high" @endif
                >
            @endforeach
        </div>
        @if(count($heroImageUrls) > 1)
            <div class="da-marketplace-hero__slideshow-controls" role="group" aria-label="Hero photo slideshow">
                <button
                    type="button"
                    class="da-marketplace-hero__slide-btn da-marketplace-hero__slide-btn--prev"
                    data-da-hero-slide="prev"
                    aria-label="Previous photo"
                >
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <button
                    type="button"
                    class="da-marketplace-hero__slide-btn da-marketplace-hero__slide-btn--next"
                    data-da-hero-slide="next"
                    aria-label="Next photo"
                >
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        @endif
        <div class="da-marketplace-hero__overlay" aria-hidden="true"></div>
        <div class="container da-marketplace-hero__content py-3 py-lg-5">
            <div class="row align-items-lg-end g-3 g-lg-5">
                <div class="col-lg-7 da-marketplace-hero__copy">
                    <h1
                        class="da-marketplace-hero-title mb-3 @if(count($heroImageUrls) > 1) da-marketplace-hero-text--slideshow-sync @endif"
                        @if(count($heroImageUrls) > 1) data-da-hero-text-sync @endif
                    >{{ $listingContent['hero_title'] }}</h1>
                    <p
                        class="da-marketplace-hero-lead mb-4 @if(count($heroImageUrls) > 1) da-marketplace-hero-text--slideshow-sync @endif"
                        @if(count($heroImageUrls) > 1) data-da-hero-text-sync @endif
                    >{{ $listingContent['hero_lead'] }}</p>
                    @if($totalListed > 0)
                        <div class="d-flex flex-wrap gap-3 da-marketplace-hero-stats">
                            <div class="da-marketplace-stat">
                                <span class="da-marketplace-stat__value">{{ number_format($totalListed) }}</span>
                                <span class="da-marketplace-stat__label">Active listings</span>
                            </div>
                            <div class="da-marketplace-stat">
                                <span class="da-marketplace-stat__value"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
                                <span class="da-marketplace-stat__label">Verified operators</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-lg-5 d-none d-lg-block" id="hero-search">
                    @include('public.home.partials.search-form', [
                        'searchUid' => 'main',
                        'listingContent' => $listingContent,
                        'cities' => $cities,
                        'rateBounds' => $rateBounds,
                        'rateMin' => $rateMin,
                        'rateMax' => $rateMax,
                    ])
                </div>
            </div>
        </div>
    </section>

    <div
        class="modal fade da-marketplace-search-modal"
        id="daMarketplaceSearchModal"
        tabindex="-1"
        aria-labelledby="daMarketplaceSearchModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header da-marketplace-search-modal__header">
                    <div>
                        <h2 class="modal-title h6 mb-0" id="daMarketplaceSearchModalLabel">{{ $listingContent['search_title'] }}</h2>
                        @if(filled($listingContent['search_subtitle']))
                            <p class="small text-muted mb-0 mt-1">{{ $listingContent['search_subtitle'] }}</p>
                        @endif
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    @include('public.home.partials.search-form', [
                        'searchUid' => 'modal',
                        'showSearchHead' => false,
                        'searchFormClass' => 'da-marketplace-search da-marketplace-search--modal',
                        'listingContent' => $listingContent,
                        'cities' => $cities,
                        'rateBounds' => $rateBounds,
                        'rateMin' => $rateMin,
                        'rateMax' => $rateMax,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container pb-5 pt-4 pt-lg-5" id="properties">
        <div class="da-marketplace-section-head d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">{{ $listingContent['listings_heading'] }}</h2>
                <p class="text-muted small mb-0">
                    @if($properties->total() > 0)
                        {{ $properties->firstItem() }}–{{ $properties->lastItem() }} of {{ $properties->total() }} results
                    @else
                        {{ $totalListed > 0 ? $listingContent['listings_no_match_hint'] : $listingContent['listings_empty_hint'] }}
                    @endif
                </p>
            </div>
            @if(request()->hasAny(['q', 'city', 'province', 'min_rate', 'max_rate']))
                <a href="{{ route('home') }}" class="btn btn-sm da-marketplace-btn-ghost"><i class="bi bi-x-lg me-1"></i>Clear filters</a>
            @endif
        </div>

        <div class="row g-4 da-marketplace-layout">
            <div class="col-lg-8">
                @if($properties->isEmpty())
                    <div class="da-panel da-empty-state py-5 px-4">
                        <i class="bi bi-house-door"></i>
                        <p class="mb-2 fw-semibold">No properties to show</p>
                        <p class="small text-muted mb-3">Try widening your search or check back soon for new dormitories.</p>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">List your property</a>
                        @endguest
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($properties as $property)
                            <div class="col-sm-6">
                                @include('public._property-card', ['property' => $property])
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <x-da-pagination :paginator="$properties" />
                    </div>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="da-panel da-marketplace-map-panel overflow-hidden sticky-lg-top">
                    <div class="da-marketplace-map-panel__head px-3 py-3">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h3 class="h6 mb-0"><i class="bi bi-map me-1 text-primary"></i>Map view</h3>
                            <span class="badge rounded-pill da-marketplace-map-badge">{{ count($mapMarkers) }} pins</span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">Tap a pin to open the listing</p>
                    </div>
                    <div class="da-map-shell">
                        <div
                            id="publicHomeMap"
                            class="da-marketplace-map"
                            data-default-lat="{{ $mapDefaults['latitude'] }}"
                            data-default-lng="{{ $mapDefaults['longitude'] }}"
                            data-default-zoom="{{ $mapDefaults['zoom'] }}"
                            role="region"
                            aria-label="Property locations map"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="publicHomeMapData">@json($mapMarkers)</script>
@endsection

@include('layouts.partials.leaflet-assets')

@push('scripts')
    <script defer src="{{ asset('js/public-home-map.js') }}@if(file_exists(public_path('js/public-home-map.js')))?v={{ filemtime(public_path('js/public-home-map.js')) }}@endif"></script>
    @if(file_exists(public_path('js/marketplace-rate-range.js')))
        <script defer src="{{ asset('js/marketplace-rate-range.js') }}?v={{ filemtime(public_path('js/marketplace-rate-range.js')) }}"></script>
    @endif
    @if(file_exists(public_path('js/marketplace-search-modal.js')))
        <script defer src="{{ asset('js/marketplace-search-modal.js') }}?v={{ filemtime(public_path('js/marketplace-search-modal.js')) }}"></script>
    @endif
    @if(file_exists(public_path('js/marketplace-hero-slideshow.js')))
        <script defer src="{{ asset('js/marketplace-hero-slideshow.js') }}?v={{ filemtime(public_path('js/marketplace-hero-slideshow.js')) }}"></script>
    @endif
@endpush
