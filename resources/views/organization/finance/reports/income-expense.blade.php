@extends('layouts.app')
@section('title', 'Income vs expense')
@section('page-title', 'Income vs expense report')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')
    <div class="da-panel p-4 mb-4">
        <div class="row text-center g-3">
            <div class="col-md-4"><div class="text-muted small">Total income</div><div class="fs-4 fw-bold text-success">₱{{ number_format($report['income'], 2) }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Total expenses</div><div class="fs-4 fw-bold text-danger">₱{{ number_format($report['expenses'], 2) }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Difference</div><div class="fs-4 fw-bold">₱{{ number_format($report['net'], 2) }}</div></div>
        </div>
    </div>
    <div class="da-panel p-4 mb-4">
        <h2 class="h6 mb-3">Chart</h2>
        <x-reports.bar-chart
            id="income-expense-trend"
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
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Month</th><th>Income</th><th>Expenses</th><th>Net</th></tr></thead>
                <tbody>@foreach($trend as $row)<tr><td>{{ $row['label'] }}</td><td>₱{{ number_format($row['income'], 2) }}</td><td>₱{{ number_format($row['expenses'], 2) }}</td><td class="fw-semibold">₱{{ number_format($row['net'], 2) }}</td></tr>@endforeach</tbody>
            </table>
        </div>
    </div>
@endsection
