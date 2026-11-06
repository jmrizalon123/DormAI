@extends('layouts.app')
@section('title', 'Tenant report')
@section('page-title', 'Tenant report')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')

    <div class="row g-3">
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Tenant records</div><div class="fs-4 fw-bold">{{ $report['total_tenants'] }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Active assignments</div><div class="fs-4 fw-bold">{{ $report['active_assignments'] }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Move-ins</div><div class="fs-4 fw-bold text-success">{{ $report['move_ins'] }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Move-outs</div><div class="fs-4 fw-bold">{{ $report['move_outs'] }}</div></div></div>
    </div>
@endsection
