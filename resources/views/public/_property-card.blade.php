@php
    use App\Support\Property\PropertyListingSettings;
    use App\Services\Property\PublicPropertyCatalog;

    $listing = PropertyListingSettings::forProperty($property);
    $catalog = app(PublicPropertyCatalog::class);
    $displayRate = $catalog->displayRate($property);
    $cover = $property->images->firstWhere('is_primary', true) ?? $property->images->first();
    $location = collect([$property->city, $property->province])->filter()->join(', ');
    $presets = collect(config('dormai.property_amenity_presets', []))->keyBy('key');
    $amenityIcons = collect($listing['amenities'] ?? [])->take(4)->map(fn ($key) => $presets->get($key))->filter();
@endphp

<article class="da-listing-card h-100">
    <a href="{{ route('public.listings.show', $property) }}" class="da-listing-card__media d-block text-decoration-none">
        @if($cover)
            <img src="{{ $cover->url() }}" alt="" class="da-listing-card__img" loading="lazy">
        @else
            <div class="da-listing-card__placeholder">
                <i class="bi bi-building" aria-hidden="true"></i>
            </div>
        @endif
        <div class="da-listing-card__badges">
            @if($displayRate !== null)
                <span class="da-listing-card__price">₱{{ number_format($displayRate, 0) }}<span class="opacity-75">/mo</span></span>
            @endif
            @if($listing['wifi_included'] ?? false)
                <span class="da-listing-card__chip"><i class="bi bi-wifi" aria-hidden="true"></i> Wi‑Fi</span>
            @endif
        </div>
    </a>
    <div class="da-listing-card__body">
        <p class="da-listing-card__org small text-muted mb-1">{{ $property->organization->name ?? 'Property' }}</p>
        <h2 class="h6 mb-1 da-listing-card__title">
            <a href="{{ route('public.listings.show', $property) }}" class="text-decoration-none stretched-link">{{ $property->name }}</a>
        </h2>
        @if($location)
            <p class="da-listing-card__location small mb-2"><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $location }}</p>
        @endif
        @if(filled($listing['tagline']))
            <p class="small da-listing-card__excerpt mb-2 line-clamp-2">{{ $listing['tagline'] }}</p>
        @elseif(filled($property->description))
            <p class="small da-listing-card__excerpt mb-2 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($property->description), 120) }}</p>
        @endif
        @if($amenityIcons->isNotEmpty())
            <div class="d-flex flex-wrap gap-1 da-listing-card__amenities">
                @foreach($amenityIcons as $amenity)
                    <span class="da-listing-card__amenity" title="{{ $amenity['label'] }}"><i class="bi {{ $amenity['icon'] }}" aria-hidden="true"></i></span>
                @endforeach
            </div>
        @endif
    </div>
</article>
