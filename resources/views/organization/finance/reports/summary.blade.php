@extends('layouts.app')
@section('title', 'Financial summary')
@section('page-title', 'Financial summary')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="da-stat-card"><div class="text-muted small">Income (payments)</div><div class="fs-4 fw-bold text-success">₱{{ number_format($report['income'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="da-stat-card"><div class="text-muted small">Approved expenses</div><div class="fs-4 fw-bold text-danger">₱{{ number_format($report['expenses'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="da-stat-card"><div class="text-muted small">Net (P&amp;L)</div><div class="fs-4 fw-bold">₱{{ number_format($report['net'], 2) }}</div></div></div>
        <div class="col-md-3"><div class="da-stat-card"><div class="text-muted small">Pending approval</div><div class="fs-4 fw-bold">{{ $expenseStats['pending_count'] }} · ₱{{ number_format($expenseStats['pending_amount'], 2) }}</div></div></div>
    </div>
    <div class="da-panel p-4 mb-4">
        <h2 class="h6 mb-3">Monthly trend</h2>
        <x-reports.bar-chart
            id="financial-summary-trend"
            :labels="$trend->pluck('label')->all()"
            :datasets="[[
                'label' => 'Income',
                'data' => $trend->pluck('income')->all(),
                'backgroundColor' => 'rgba(25, 135, 84, 0.6)',
            ], [
                'label' => 'Expenses',
                'data' => $trend->pluck('expenses')->all(),
                'backgroundColor' => 'rgba(220, 53, 69, 0.55)',
            ]]"
        />
    </div>
    <div class="da-panel p-4">
        <h2 class="h6">Monthly detail</h2>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Month</th><th>Income</th><th>Expenses</th><th>Net</th></tr></thead>
                <tbody>@foreach($trend as $row)<tr><td>{{ $row['label'] }}</td><td>₱{{ number_format($row['income'], 2) }}</td><td>₱{{ number_format($row['expenses'], 2) }}</td><td class="fw-semibold">₱{{ number_format($row['net'], 2) }}</td></tr>@endforeach</tbody>
            </table>
        </div>
    </div>
@endsection
