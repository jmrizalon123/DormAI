@extends('layouts.app')
@section('title', 'Profit & loss')
@section('page-title', 'Profit & loss')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')
    <div class="row g-4">
        <div class="col-md-6">
            <div class="da-panel p-4">
                <h2 class="h6">Income</h2>
                <p class="fs-3 fw-bold text-success mb-3">₱{{ number_format($report['income'], 2) }}</p>
                <ul class="list-unstyled small mb-0">@forelse($report['income_by_method'] as $method => $total)<li class="d-flex justify-content-between py-1 border-bottom"><span>{{ strtoupper($method) }}</span><span>₱{{ number_format($total, 2) }}</span></li>@empty<li class="text-muted">No payments in this period.</li>@endforelse</ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="da-panel p-4">
                <h2 class="h6">Expenses (approved)</h2>
                <p class="fs-3 fw-bold text-danger mb-3">₱{{ number_format($report['expenses'], 2) }}</p>
                <ul class="list-unstyled small mb-0">@forelse($report['expenses_by_category'] as $name => $total)<li class="d-flex justify-content-between py-1 border-bottom"><span>{{ $name }}</span><span>₱{{ number_format($total, 2) }}</span></li>@empty<li class="text-muted">No approved expenses in this period.</li>@endforelse</ul>
            </div>
        </div>
        <div class="col-12"><div class="da-panel p-4 text-center"><div class="text-muted small">Net profit / loss</div><div class="display-6 fw-bold">₱{{ number_format($report['net'], 2) }}</div></div></div>
    </div>
@endsection
