@extends('layouts.marketplace')

@section('title', $property->name.' — '.config('app.name'))

@section('content')
    @php
        use App\Support\Property\PropertyListingSettings;

        $presets = collect(config('dormai.property_amenity_presets', []))->keyBy('key');
        $amenityLabels = collect($listing['amenities'] ?? [])->map(fn ($key) => $presets->get($key))->filter();
        $cover = $property->images->firstWhere('is_primary', true) ?? $property->images->first();
        $postalAddress = collect([
            $property->address_line1,
            $property->address_line2,
            collect([$property->city, $property->province])->filter()->join(', '),
            $property->postal_code,
            $property->country !== 'PH' ? $property->country : null,
        ])->filter()->join("\n");
        $checkIn = PropertyListingSettings::formatListingTime($listing['check_in_time'] ?? null);
        $checkOut = PropertyListingSettings::formatListingTime($listing['check_out_time'] ?? null);
        $genderLabel = PropertyListingSettings::genderPolicyLabel($listing['gender_policy'] ?? 'mixed');
    @endphp

    <div class="container py-4 py-lg-5 da-marketplace-detail">
        <nav aria-label="Breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Listings</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $property->name }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Gallery --}}
                <div class="da-listing-gallery da-panel overflow-hidden mb-4">
                    @if($property->images->isNotEmpty())
                        @if($property->images->count() === 1)
                            <div class="ratio ratio-21x9">
                                <img src="{{ $cover->url() }}" alt="" class="object-fit-cover">
                            </div>
                        @else
                            <div class="row g-0">
                                <div class="col-12 col-md-8">
                                    <div class="da-listing-gallery__hero ratio ratio-4x3">
                                        <img src="{{ $cover->url() }}" alt="" class="object-fit-cover" id="daListingHeroImg">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 da-listing-gallery__thumbs">
                                    @foreach($property->images->take(4) as $image)
                                        <button type="button" class="ratio ratio-4x3 da-listing-gallery__thumb-btn border-0 p-0" data-hero-src="{{ $image->url() }}" aria-label="View photo">
                                            <img src="{{ $image->url() }}" alt="" class="object-fit-cover" loading="lazy">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @if($property->images->count() > 4)
                                <div class="p-3 border-top da-listing-gallery__grid">
                                    <div class="row g-2">
                                        @foreach($property->images->skip(4) as $image)
                                            <div class="col-4 col-sm-3">
                                                <button type="button" class="ratio ratio-4x3 da-listing-gallery__thumb-btn border-0 p-0 w-100 rounded overflow-hidden" data-hero-src="{{ $image->url() }}" aria-label="View photo">
                                                    <img src="{{ $image->url() }}" alt="" class="object-fit-cover" loading="lazy">
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="da-listing-card__placeholder da-listing-gallery__empty">
                            <i class="bi bi-images" aria-hidden="true"></i>
                            <span class="small">Photos coming soon</span>
                        </div>
                    @endif
                </div>

                {{-- Overview --}}
                <div class="da-panel mb-4 overflow-hidden">
                    <div class="p-4 pb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <h1 class="h3 mb-0">{{ $property->name }}</h1>
                        @if(filled($property->code))
                            <span class="badge text-bg-light border font-monospace">{{ $property->code }}</span>
                        @endif
                    </div>
                    @if(filled($listing['tagline']))
                        <p class="lead fs-6 text-secondary mb-3">{{ $listing['tagline'] }}</p>
                    @endif
                    @if(filled($property->description))
                        <h2 class="h6 fw-bold mb-2 mt-3">About this property</h2>
                        <div class="text-secondary mb-0">{!! nl2br(e($property->description)) !!}</div>
                    @endif
                    </div>
                </div>

                {{-- Quick facts --}}
                <div class="da-panel mb-4 overflow-hidden">
                    <div class="da-panel__head"><h2>Property details</h2></div>
                    <div class="p-4 pt-3">
                    <dl class="row da-listing-specs mb-0">
                        <dt class="col-sm-4 text-muted">Managed by</dt>
                        <dd class="col-sm-8">{{ $property->organization->name }}</dd>

                        @if($displayRate !== null)
                            <dt class="col-sm-4 text-muted">Starting rate</dt>
                            <dd class="col-sm-8 fw-semibold text-primary">₱{{ number_format($displayRate, 2) }} / month</dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Rooms</dt>
                        <dd class="col-sm-8">{{ $occupancy['rooms'] }}</dd>

                        <dt class="col-sm-4 text-muted">Bed spaces</dt>
                        <dd class="col-sm-8">{{ $occupancy['bed_spaces'] }}</dd>

                        <dt class="col-sm-4 text-muted">Available now</dt>
                        <dd class="col-sm-8">{{ $occupancy['available'] }} bed{{ $occupancy['available'] === 1 ? '' : 's' }}</dd>

                        @if($occupancy['occupancy_rate'] !== null)
                            <dt class="col-sm-4 text-muted">Occupancy</dt>
                            <dd class="col-sm-8">{{ $occupancy['occupancy_rate'] }}%</dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Gender policy</dt>
                        <dd class="col-sm-8">{{ $genderLabel }}</dd>

                        @if(filled($listing['min_stay_months']))
                            <dt class="col-sm-4 text-muted">Minimum stay</dt>
                            <dd class="col-sm-8">{{ $listing['min_stay_months'] }} month{{ (int) $listing['min_stay_months'] === 1 ? '' : 's' }}</dd>
                        @endif

                        @if($checkIn || $checkOut)
                            <dt class="col-sm-4 text-muted">Check-in / out</dt>
                            <dd class="col-sm-8">
                                @if($checkIn){{ $checkIn }}@endif
                                @if($checkIn && $checkOut) · @endif
                                @if($checkOut){{ $checkOut }}@endif
                            </dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Wi‑Fi</dt>
                        <dd class="col-sm-8">{{ ($listing['wifi_included'] ?? false) ? 'Included in rent' : 'Ask owner' }}</dd>

                        <dt class="col-sm-4 text-muted">Utilities</dt>
                        <dd class="col-sm-8">{{ ($listing['utilities_included'] ?? false) ? 'Included in rent' : 'Billed separately' }}</dd>
                    </dl>
                    </div>
                </div>

                @if($amenityLabels->isNotEmpty() || count($listing['custom_amenities'] ?? []) > 0)
                    <div class="da-panel mb-4 overflow-hidden">
                        <div class="da-panel__head"><h2>Amenities &amp; features</h2></div>
                        <div class="p-4 pt-3">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($amenityLabels as $amenity)
                                <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi {{ $amenity['icon'] }} me-1"></i>{{ $amenity['label'] }}</span>
                            @endforeach
                            @foreach($listing['custom_amenities'] ?? [] as $custom)
                                <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $custom }}</span>
                            @endforeach
                        </div>
                        </div>
                    </div>
                @endif

                @if(count($listing['highlights'] ?? []) > 0)
                    <div class="da-panel mb-4 overflow-hidden">
                        <div class="da-panel__head"><h2>Highlights</h2></div>
                        <div class="p-4 pt-3">
                        <ul class="mb-0 ps-3">
                            @foreach($listing['highlights'] as $highlight)
                                <li class="mb-1">{{ $highlight }}</li>
                            @endforeach
                        </ul>
                        </div>
                    </div>
                @endif

                @if(filled($listing['nearby_landmarks']))
                    <div class="da-panel mb-4 overflow-hidden">
                        <div class="da-panel__head"><h2>Nearby landmarks &amp; commute</h2></div>
                        <div class="p-4 pt-3 text-secondary">{!! nl2br(e($listing['nearby_landmarks'])) !!}</div>
                    </div>
                @endif

                @if(filled($listing['house_rules']))
                    <div class="da-panel mb-4 overflow-hidden">
                        <div class="da-panel__head"><h2>House rules</h2></div>
                        <div class="p-4 pt-3 text-secondary">{!! nl2br(e($listing['house_rules'])) !!}</div>
                    </div>
                @endif

                <div class="da-panel mb-4 overflow-hidden">
                    <div class="da-panel__head"><h2>Address &amp; contact</h2></div>
                    <div class="p-4 pt-3">
                    @if(filled($postalAddress))
                        <p class="mb-3 text-secondary" style="white-space: pre-line;"><i class="bi bi-geo-alt me-1 text-muted"></i>{{ $postalAddress }}</p>
                    @endif
                    <ul class="list-unstyled small mb-0">
                        @if(filled($property->phone))
                            <li class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i><a href="tel:{{ preg_replace('/\s+/', '', $property->phone) }}" class="text-decoration-none">{{ $property->phone }}</a></li>
                        @endif
                        @if(filled($property->email))
                            <li><i class="bi bi-envelope me-2 text-muted"></i><a href="mailto:{{ $property->email }}" class="text-decoration-none">{{ $property->email }}</a></li>
                        @endif
                    </ul>
                    </div>
                </div>

                @if($property->hasCoordinates())
                    <div class="da-panel overflow-hidden mb-4">
                        <div class="da-panel__head"><h2>Location on map</h2></div>
                        <div class="da-map-shell">
                            <div
                                id="publicListingMap"
                                class="da-marketplace-map da-marketplace-map--detail"
                                data-lat="{{ $property->latitude }}"
                                data-lng="{{ $property->longitude }}"
                                data-zoom="15"
                                role="region"
                                aria-label="Property location"
                            ></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="da-panel da-listing-cta-card p-4 sticky-lg-top mb-4">
                    @if($displayRate !== null)
                        <div class="mb-3 pb-3 border-bottom">
                            <span class="text-muted small">Starting from</span>
                            <div class="fs-3 fw-bold text-primary da-listing-cta-card__price">₱{{ number_format($displayRate, 0) }}<span class="fs-6 fw-normal text-muted">/month</span></div>
                        </div>
                    @endif
                    <ul class="list-unstyled small mb-4 da-listing-cta-card__stats">
                        <li class="mb-2 d-flex justify-content-between"><span class="text-muted">Available beds</span><strong>{{ $occupancy['available'] }}</strong></li>
                        <li class="mb-2 d-flex justify-content-between"><span class="text-muted">Total beds</span><strong>{{ $occupancy['bed_spaces'] }}</strong></li>
                        <li class="mb-2 d-flex justify-content-between"><span class="text-muted">Gender</span><strong>{{ $genderLabel }}</strong></li>
                        @if(filled($listing['min_stay_months']))
                            <li class="mb-2 d-flex justify-content-between"><span class="text-muted">Min. stay</span><strong>{{ $listing['min_stay_months'] }} mo</strong></li>
                        @endif
                    </ul>
                    <a href="{{ route('public.stay.index', $property->organization->slug) }}" class="btn btn-primary w-100 mb-2">View available beds &amp; reserve</a>
                    @if(filled($property->phone))
                        <a href="tel:{{ preg_replace('/\s+/', '', $property->phone) }}" class="btn btn-outline-secondary w-100 mb-2">Call property</a>
                    @endif
                    <p class="text-muted small mb-0 text-center">Listed by {{ $property->organization->name }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(! empty($listingAssistantEnabled))
        <x-listing-assistant-widget
            :property="$property"
            :endpoint="route('public.listings.assistant', $property)"
            widget-id="daListingAssistant"
            title="Listing assistant"
            :subtitle="! empty($listingAiCompanionEnabled)
                ? 'Your companion for this listing — optional AI explains what’s on file and suggests next steps. Facts come from our database; confirm important details with the operator.'
                : 'Answers from this listing’s saved data (local). Confirm important details with the operator.'"
            fab-label="Ask about this listing"
            intro="Hi — ask about rate, location, amenities, policies, or how to reserve a bed for this listing."
            placeholder="e.g. What’s the rate? Where is it? Is Wi‑Fi included?"
            aria-label="Listing assistant panel"
            quick-replies="What is the monthly rate?|How many beds are available?|Recommend similar listings"
        />
    @endif
@endsection

@if($property->images->count() > 1)
    @push('scripts')
        <script>
            document.querySelectorAll('.da-listing-gallery__thumb-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var hero = document.getElementById('daListingHeroImg');
                    var src = btn.getAttribute('data-hero-src');
                    if (hero && src) {
                        hero.src = src;
                    }
                });
            });
        </script>
    @endpush
@endif

@if($property->hasCoordinates())
    @include('layouts.partials.leaflet-assets')
    @push('scripts')
        <script defer src="{{ asset('js/public-listing-map.js') }}@if(file_exists(public_path('js/public-listing-map.js')))?v={{ filemtime(public_path('js/public-listing-map.js')) }}@endif"></script>
    @endpush
@endif

