@extends('layouts.app')
@section('title', 'Reservation report')
@section('page-title', 'Reservation report')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="text-muted small">Requests (period)</div><div class="fs-4 fw-bold">{{ $report['total'] }}</div></div></div>
        <div class="col-12 col-md-4"><div class="da-stat-card"><div class="text-muted small">Check-in conversion</div><div class="fs-4 fw-bold">{{ $report['conversion_rate'] !== null ? $report['conversion_rate'].'%' : '—' }}</div></div></div>
    </div>

    <div class="da-panel p-4">
        <h2 class="h6 mb-3">By status</h2>
        <x-reports.bar-chart
            id="reservations-by-status"
            :labels="collect(\App\Enums\ReservationStatus::cases())->map->label()->all()"
            :datasets="[[
                'label' => 'Count',
                'data' => collect(\App\Enums\ReservationStatus::cases())->map(fn ($s) => $report['by_status'][$s->value] ?? 0)->all(),
                'backgroundColor' => 'rgba(111, 66, 193, 0.65)',
            ]]"
        />
    </div>
@endsection
