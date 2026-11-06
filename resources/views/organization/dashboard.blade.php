@extends('layouts.app')

@section('title', 'Dashboard — '.config('app.name'))
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Portfolio overview · '.$organization?->name)

@section('page-actions')
    @can('viewOrganizationReports')
        <a href="{{ route('organization.reports.hub') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pie-chart me-1"></i> Reports
        </a>
    @endcan
@endsection

@section('content')
    <div class="da-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="h4 mb-0">Welcome back</h2>
            <p class="mb-0">Live occupancy, collections, and reservation trends at a glance.</p>
        </div>
        @can('viewAny', App\Models\Property::class)
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('organization.properties.index') }}" class="btn btn-light btn-sm">Properties</a>
                @can('viewAny', App\Models\Reservation::class)
                    <a href="{{ route('organization.reservations.index') }}" class="btn btn-outline-light btn-sm">Reservations</a>
                @endcan
            </div>
        @endcan
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        @foreach ($featuredCharts as $featured)
            @php
                $featuredPayload = [
                    'type' => $featured['type'],
                    'labels' => $featured['labels'],
                    'datasets' => $featured['datasets'],
                ];
            @endphp
            <div @class([
                'col-12',
                'col-lg-6' => count($featuredCharts) <= 2,
                'col-xl-4' => count($featuredCharts) >= 3,
            ])>
                <div class="da-panel da-dashboard-chart-panel p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h2 class="h6 mb-1">{{ $featured['title'] }}</h2>
                            <p class="text-muted small mb-0">{{ $featured['subtitle'] }}</p>
                        </div>
                    </div>
                    <div class="da-dashboard-chart-wrap">
                        <canvas
                            id="{{ $featured['id'] }}"
                            class="da-dashboard-chart"
                            data-da-featured='@json($featuredPayload)'
                        ></canvas>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 g-xl-4">
        @foreach ($chartCards as $card)
            <div class="col-sm-6 col-xl-4">
                <x-ui.stat-chart-card
                    :chart-id="$card['id']"
                    :label="$card['label']"
                    :value="$card['value']"
                    :icon="$card['icon']"
                    :accent="$card['accent']"
                    :caption="$card['caption']"
                    :trend="$card['trend']"
                    :chart="$card['chart']"
                />
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script defer src="{{ asset('js/dashboard-charts.js') }}?v={{ filemtime(public_path('js/dashboard-charts.js')) }}"></script>
@endpush
