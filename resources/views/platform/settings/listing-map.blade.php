@extends('layouts.app')
@section('title', 'Listing map defaults')
@section('page-title', 'Platform settings')
@section('page-subtitle', 'Default map focus for the public listings marketplace')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="da-panel p-4">
                <h2 class="h6 mb-1">Public listings map focus</h2>
                <p class="text-muted small mb-4">
                    Centers the home page map and sets the default view before visitors zoom to property pins.
                    Config fallback:
                    <strong>{{ $config['label'] }}</strong>
                    ({{ number_format($config['latitude'], 4) }}, {{ number_format($config['longitude'], 4) }}).
                </p>

                <form method="POST" action="{{ route('platform.settings.listing-map.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="listingMapLabel">Location label</label>
                            <input type="text" name="label" id="listingMapLabel" class="form-control" maxlength="120" value="{{ old('label', $map['label']) }}" placeholder="Metro Manila">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="listingMapLatitude">Latitude</label>
                            <input type="number" step="any" name="latitude" id="listingMapLatitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $map['latitude']) }}" required>
                            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="listingMapLongitude">Longitude</label>
                            <input type="number" step="any" name="longitude" id="listingMapLongitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $map['longitude']) }}" required>
                            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="listingMapZoom">Default zoom</label>
                            <input type="number" name="zoom" id="listingMapZoom" min="3" max="18" class="form-control @error('zoom') is-invalid @enderror" value="{{ old('zoom', $map['zoom']) }}" required>
                            @error('zoom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="text-muted small mb-2" id="listingMapCoords">
                                {{ number_format((float) $map['latitude'], 5) }}, {{ number_format((float) $map['longitude'], 5) }}
                            </div>
                            <div class="da-map-shell">
                                <div
                                    id="listingMapPicker"
                                    class="da-property-map-picker rounded border"
                                    data-da-map-picker
                                    data-lat-input="listingMapLatitude"
                                    data-lng-input="listingMapLongitude"
                                    data-coords-label="listingMapCoords"
                                    data-default-lat="{{ $map['latitude'] }}"
                                    data-default-lng="{{ $map['longitude'] }}"
                                    data-default-zoom="{{ $map['zoom'] }}"
                                    data-empty-label="Click the map to set listing map focus"
                                ></div>
                            </div>
                            <div class="form-text mt-2">Drag the pin or click the map. Use full-screen (top-right) for precision.</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Save listing map defaults</button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">Preview marketplace</a>
                    </div>
                </form>

                <form
                    method="POST"
                    action="{{ route('platform.settings.listing-map.reset') }}"
                    class="mt-3"
                    data-da-confirm="Reset to values in config/dormai.php?"
                    data-da-confirm-title="Reset listing map defaults"
                >
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reset to config default</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="da-panel p-4 h-100">
                <h2 class="h6 mb-3">Where this applies</h2>
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-2">Public home page map before users open a property pin.</li>
                    <li class="mb-2">Default center when the map has no geocoded listings in view.</li>
                    <li class="mb-2">System fallback for organization property map settings.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@include('layouts.partials.leaflet-assets')

@push('scripts')
    <script defer src="{{ asset('js/property-map-picker.js') }}@if(file_exists(public_path('js/property-map-picker.js')))?v={{ filemtime(public_path('js/property-map-picker.js')) }}@endif"></script>
@endpush
