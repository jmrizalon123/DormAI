@extends('layouts.app')
@section('title', 'Map settings')
@section('page-title', 'Organization settings')
@section('page-subtitle', 'Default map center for new property pins · '.$organization->name)

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="da-panel p-4">
                <h2 class="h6 mb-1">Default map target</h2>
                <p class="text-muted small mb-4">
                    Used when adding properties (Location tab) before a pin is placed. System default:
                    <strong>{{ $system['label'] }}</strong> ({{ number_format($system['latitude'], 4) }}, {{ number_format($system['longitude'], 4) }}).
                </p>

                <form method="POST" action="{{ route('organization.settings.map.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Location label</label>
                            <input type="text" name="label" class="form-control" maxlength="120" value="{{ old('label', $map['label']) }}" placeholder="Catbalogan City, Samar">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" id="orgDefaultLatitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $map['latitude']) }}" required>
                            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" id="orgDefaultLongitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $map['longitude']) }}" required>
                            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default zoom</label>
                            <input type="number" name="zoom" id="orgDefaultZoom" min="3" max="18" class="form-control @error('zoom') is-invalid @enderror" value="{{ old('zoom', $map['zoom']) }}" required>
                            @error('zoom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="text-muted small mb-2" id="orgDefaultMapCoords">
                                {{ number_format((float) $map['latitude'], 5) }}, {{ number_format((float) $map['longitude'], 5) }}
                            </div>
                            <div class="da-map-shell">
                            <div
                                id="orgDefaultMapPicker"
                                class="da-property-map-picker rounded border"
                                data-da-map-picker
                                data-lat-input="orgDefaultLatitude"
                                data-lng-input="orgDefaultLongitude"
                                data-coords-label="orgDefaultMapCoords"
                                data-default-lat="{{ $map['latitude'] }}"
                                data-default-lng="{{ $map['longitude'] }}"
                                data-default-zoom="{{ $map['zoom'] }}"
                                data-empty-label="Click the map to set default center"
                            ></div>
                            </div>
                            <div class="form-text mt-2">Click or drag the pin to set your organization&apos;s default map center. Use the full-screen control (top-right) to expand the map.</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Save map settings</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('organization.settings.map.reset') }}" class="mt-3" data-da-confirm="Reset map defaults to Catbalogan City, Samar?" data-da-confirm-title="Reset map defaults">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reset to system default</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="da-panel p-4 h-100">
                <h2 class="h6 mb-3">How this is used</h2>
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-2">New property forms open the map centered on this location.</li>
                    <li class="mb-2">Each property still gets its own pin when you save.</li>
                    <li>Useful when all your dorms are in one city (e.g. Catbalogan) but at different addresses.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@include('layouts.partials.leaflet-assets')

@push('scripts')
    <script defer src="{{ asset('js/property-map-picker.js') }}@if(file_exists(public_path('js/property-map-picker.js')))?v={{ filemtime(public_path('js/property-map-picker.js')) }}@endif"></script>
@endpush
