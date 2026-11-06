@extends('layouts.app')
@section('title', 'Properties — '.config('app.name'))
@section('page-title', 'Properties')
@section('page-subtitle', 'Manage dormitories and boarding house locations')
@section('page-actions')
    <a href="{{ route('organization.properties.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add property</a>
@endsection
@section('content')
    <div class="da-properties-index-layout row g-4 g-lg-0">
        <div class="col-lg-8 da-properties-index-list">
            <div class="da-panel da-properties-index-list-panel">
                <div class="px-3 py-2 border-bottom">
                    <h2 class="h6 mb-0">All properties</h2>
                    <p class="text-muted small mb-0">Select a row or a map pin to open a property.</p>
                </div>
                <div class="table-responsive da-table-wrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <x-da-table-num-th />
                                <th>Property</th>
                                <th>Location</th>
                                <th class="text-center" style="width: 3rem;" aria-label="Map pin"><i class="bi bi-geo-alt text-muted" title="Map pin"></i></th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($properties as $property)
                                <tr>
                                    <x-da-table-num-td :paginator="$properties" :row="$loop->index" />
                                    <td>
                                        <div class="fw-semibold">{{ $property->name }}</div>
                                        @if($property->code)<div class="small text-muted">{{ $property->code }}</div>@endif
                                    </td>
                                    <td class="text-muted small">{{ collect([$property->city, $property->province])->filter()->join(', ') ?: '—' }}</td>
                                    <td class="text-center">
                                        @if($property->hasCoordinates())
                                            <span class="text-success" title="Pinned on map"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i><span class="visually-hidden">Pinned</span></span>
                                        @else
                                            <span class="text-muted opacity-50" title="No map pin"><i class="bi bi-geo-alt" aria-hidden="true"></i><span class="visually-hidden">Not pinned</span></span>
                                        @endif
                                    </td>
                                    <td><span class="badge text-bg-{{ $property->status->badgeClass() }}">{{ $property->status->label() }}</span></td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('organization.properties.show', $property) }}">View</a>
                                            @can('update', $property)
                                                <a class="btn btn-sm btn-primary" href="{{ route('organization.properties.edit', $property) }}">Edit</a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-5">No properties yet. Add your first location to get started.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-da-pagination :paginator="$properties" />
            </div>
        </div>

        <div class="col-lg-4 da-properties-index-map">
            @include('organization.properties._portfolio-map')
        </div>
    </div>
@endsection

@include('layouts.partials.leaflet-assets')

@push('scripts')
    <script defer src="{{ asset('js/properties-index-map.js') }}@if(file_exists(public_path('js/properties-index-map.js')))?v={{ filemtime(public_path('js/properties-index-map.js')) }}@endif"></script>
@endpush
