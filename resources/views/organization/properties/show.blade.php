@extends('layouts.app')
@section('title', $property->name)
@section('page-title', $property->name)
@section('page-subtitle', 'Property overview')
@section('page-actions')
    <a href="{{ route('organization.properties.edit', $property) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    <a href="{{ route('organization.properties.buildings.create', $property) }}" class="btn btn-primary btn-sm">Add building</a>
@endsection
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Rooms</div><div class="fs-4 fw-semibold">{{ $summary['rooms'] }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Bed spaces</div><div class="fs-4 fw-semibold">{{ $summary['bed_spaces'] }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Available beds</div><div class="fs-4 fw-semibold">{{ $summary['available'] }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Occupancy</div><div class="fs-4 fw-semibold">{{ $summary['occupancy_rate'] !== null ? $summary['occupancy_rate'].'%' : '—' }}</div></div></div>
    </div>
    <div class="row g-4 da-property-show-layout">
        <div class="col-lg-8 da-property-show-main">
            @include('organization.properties._listing-summary')
            <div class="da-panel overflow-hidden mb-4">
                <div class="px-3 py-2 border-bottom">
                    <h2 class="h6 mb-0">Buildings</h2>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($property->buildings as $building)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><div class="fw-medium">{{ $building->name }}</div><div class="small text-muted">{{ $building->floors->count() }} floors</div></div>
                            <a href="{{ route('organization.buildings.show', $building) }}" class="btn btn-sm btn-outline-secondary">Manage</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No buildings yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-4 da-property-show-aside d-flex flex-column gap-4">
            <div class="da-panel overflow-hidden">
                <div class="px-3 py-2 border-bottom">
                    <h2 class="h6 mb-0">Photos</h2>
                </div>
                <div class="p-3">
                    @forelse ($property->images as $image)
                        <div class="mb-3">
                            <img src="{{ $image->url() }}" alt="" class="img-fluid rounded border">
                            <form method="POST" action="{{ route('organization.properties.images.destroy', [$property, $image]) }}" class="mt-1">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted small mb-3">No images uploaded.</p>
                    @endforelse
                    <form method="POST" action="{{ route('organization.properties.images.store', $property) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="image" class="form-control mb-2" accept="image/*" required>
                        <button class="btn btn-sm btn-primary">Upload image</button>
                    </form>
                </div>
            </div>

            @if($property->hasCoordinates())
                <div class="da-panel overflow-hidden da-property-show-map-panel mt-lg-auto">
                    <div class="px-3 py-2 border-bottom">
                        <h2 class="h6 mb-0">Location on map</h2>
                    </div>
                    <div class="p-2">
                        @include('organization.properties._map-display', ['property' => $property, 'height' => '240px'])
                    </div>
                </div>
            @else
                <div class="da-panel overflow-hidden da-property-show-map-panel mt-lg-auto">
                    <div class="px-3 py-2 border-bottom">
                        <h2 class="h6 mb-0">Location on map</h2>
                    </div>
                    <div class="p-3">
                        <p class="text-muted small mb-2">No map pin for this property yet.</p>
                        <a href="{{ route('organization.properties.edit', $property) }}#pane-location" class="btn btn-sm btn-outline-primary">Add location</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
