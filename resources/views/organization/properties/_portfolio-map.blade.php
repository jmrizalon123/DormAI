@php
    $pinnedCount = $mapProperties->count();
    $unpinnedMapCount = max(0, ($totalPropertyCount ?? 0) - $pinnedCount);
@endphp

<div class="da-panel da-properties-portfolio-map-panel overflow-hidden h-100">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2 border-bottom">
        <div>
            <h2 class="h6 mb-0">Portfolio map</h2>
            <p class="text-muted small mb-0">
                @if ($pinnedCount > 0)
                    {{ $pinnedCount }} {{ $pinnedCount === 1 ? 'property' : 'properties' }} with map pins
                @else
                    Pin locations on each property to see them here
                @endif
            </p>
        </div>
        @if ($unpinnedMapCount > 0)
            <span class="badge text-bg-light border text-muted">{{ $unpinnedMapCount }} without pins</span>
        @endif
    </div>
    <div class="da-map-shell da-properties-portfolio-map-shell flex-grow-1">
        <div
            id="organizationPropertiesMap"
            class="da-properties-portfolio-map"
            data-default-lat="{{ $mapDefaults['latitude'] }}"
            data-default-lng="{{ $mapDefaults['longitude'] }}"
            data-default-zoom="{{ $mapDefaults['zoom'] }}"
            role="region"
            aria-label="Map of property locations"
        ></div>
    </div>
    @if ($pinnedCount === 0)
        <p class="small text-muted mb-0 px-3 py-2 border-top">
            Open a property, go to <strong>Location &amp; contact</strong>, and drop a pin on the map.
        </p>
    @endif
</div>

<script type="application/json" id="organizationPropertiesMapData">@json($mapMarkers)</script>
