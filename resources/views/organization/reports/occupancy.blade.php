@extends('layouts.app')
@section('title', 'Occupancy report')
@section('page-title', 'Occupancy report')
@section('page-subtitle', 'Current bed utilization')
@section('content')
    @include('organization.finance.reports._export-actions')

    @php($s = $report['summary'])
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2"><div class="da-stat-card"><div class="text-muted small">Bed spaces</div><div class="fs-4 fw-bold">{{ $s['total'] }}</div></div></div>
        <div class="col-6 col-md-2"><div class="da-stat-card"><div class="text-muted small">Occupied</div><div class="fs-4 fw-bold">{{ $s['occupied'] }}</div></div></div>
        <div class="col-6 col-md-2"><div class="da-stat-card"><div class="text-muted small">Available</div><div class="fs-4 fw-bold text-success">{{ $s['available'] }}</div></div></div>
        <div class="col-6 col-md-2"><div class="da-stat-card"><div class="text-muted small">Reserved</div><div class="fs-4 fw-bold">{{ $s['reserved'] }}</div></div></div>
        <div class="col-6 col-md-2"><div class="da-stat-card"><div class="text-muted small">Maintenance</div><div class="fs-4 fw-bold">{{ $s['maintenance'] }}</div></div></div>
        <div class="col-6 col-md-2"><div class="da-stat-card"><div class="text-muted small">Occupancy rate</div><div class="fs-4 fw-bold">{{ $s['rate'] !== null ? $s['rate'].'%' : '—' }}</div></div></div>
    </div>

    <div class="da-panel p-4 mb-4">
        <h2 class="h6 mb-3">By property</h2>
        <x-reports.bar-chart
            id="occupancy-by-property"
            :labels="$report['by_property']->pluck('property')->all()"
            :datasets="[[
                'label' => 'Occupied beds',
                'data' => $report['by_property']->pluck('occupied')->all(),
                'backgroundColor' => 'rgba(13, 110, 253, 0.65)',
            ], [
                'label' => 'Available beds',
                'data' => $report['by_property']->pluck('available')->all(),
                'backgroundColor' => 'rgba(25, 135, 84, 0.55)',
            ]]"
        />
    </div>

    <div class="da-panel p-4">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Rooms</th>
                        <th>Bed spaces</th>
                        <th>Occupied</th>
                        <th>Available</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['by_property'] as $row)
                        <tr>
                            <td>{{ $row['property'] }}</td>
                            <td>{{ $row['rooms'] }}</td>
                            <td>{{ $row['bed_spaces'] }}</td>
                            <td>{{ $row['occupied'] }}</td>
                            <td>{{ $row['available'] }}</td>
                            <td>{{ $row['occupancy_rate'] !== null ? $row['occupancy_rate'].'%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No properties yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
