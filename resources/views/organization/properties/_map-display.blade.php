@props(['property', 'height' => '280px'])

@if($property->hasCoordinates())
    <div class="da-map-shell">
        <div
            class="da-property-map-display rounded border"
            id="propertyMapDisplay-{{ $property->id }}"
            style="height: {{ $height }};"
            data-lat="{{ $property->latitude }}"
            data-lng="{{ $property->longitude }}"
            data-title="{{ $property->name }}"
        ></div>
    </div>
    @once
        @include('layouts.partials.leaflet-assets')
        @push('scripts')
            <script defer src="{{ asset('js/property-map-display.js') }}@if(file_exists(public_path('js/property-map-display.js')))?v={{ filemtime(public_path('js/property-map-display.js')) }}@endif"></script>
        @endpush
    @endonce
@endif
