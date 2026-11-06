@php
    $listing = \App\Support\Property\PropertyListingSettings::forProperty($property);
    $presets = collect(config('dormai.property_amenity_presets', []))->keyBy('key');
    $amenityLabels = collect($listing['amenities'] ?? [])
        ->map(fn ($key) => $presets->get($key))
        ->filter();
@endphp

@if(filled($listing['tagline']) || count($listing['highlights'] ?? []) || $amenityLabels->isNotEmpty() || filled($listing['starting_rate']))
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-medium d-flex justify-content-between align-items-center">
            <span>Listing & amenities</span>
            <a href="{{ route('organization.properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
        </div>
        <div class="card-body">
            @if(filled($listing['tagline']))
                <p class="lead fs-6 mb-3">{{ $listing['tagline'] }}</p>
            @endif
            @if(filled($listing['starting_rate']))
                <p class="mb-3"><span class="text-muted small">From</span> <span class="fw-semibold">₱{{ number_format((float) $listing['starting_rate'], 2) }}</span> <span class="text-muted small">/ month</span></p>
            @endif
            @if($amenityLabels->isNotEmpty() || count($listing['custom_amenities'] ?? []) > 0)
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($amenityLabels as $amenity)
                        <span class="badge rounded-pill text-bg-light border"><i class="bi {{ $amenity['icon'] }} me-1"></i>{{ $amenity['label'] }}</span>
                    @endforeach
                    @foreach($listing['custom_amenities'] ?? [] as $custom)
                        <span class="badge rounded-pill text-bg-light border">{{ $custom }}</span>
                    @endforeach
                </div>
            @endif
            @if(count($listing['highlights'] ?? []) > 0)
                <ul class="small mb-0 ps-3">
                    @foreach($listing['highlights'] as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
