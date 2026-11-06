@extends('layouts.app')
@section('title', 'Utility report')
@section('page-title', 'Utility consumption report')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="da-stat-card"><div class="small text-muted">Electricity (kWh)</div><div class="fs-4 fw-bold">{{ number_format($summary['total_consumption']['electricity'] ?? 0, 3) }}</div></div></div>
        <div class="col-md-4"><div class="da-stat-card"><div class="small text-muted">Water (units)</div><div class="fs-4 fw-bold">{{ number_format($summary['total_consumption']['water'] ?? 0, 3) }}</div></div></div>
        <div class="col-md-4"><div class="da-stat-card"><div class="small text-muted">Total allocated bill</div><div class="fs-4 fw-bold">₱{{ number_format($summary['total_billed'], 2) }}</div></div></div>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Property</th><th>Readings</th><th>Consumption</th><th>Allocated</th></tr></thead>
                <tbody>@forelse($summary['by_property'] as $row)<tr><td>{{ $row['property'] }}</td><td>{{ $row['readings'] }}</td><td>{{ number_format($row['consumption'], 3) }}</td><td>₱{{ number_format($row['billed'], 2) }}</td></tr>@empty<tr><td colspan="4" class="text-muted p-3">No readings in this period.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>
@endsection
