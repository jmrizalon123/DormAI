@extends('layouts.app')
@section('title', 'Payment collection')
@section('page-title', 'Payment collection')
@section('page-subtitle', $from->format('M j, Y').' – '.$to->format('M j, Y'))
@section('content')
    @include('organization.finance.reports._date-filter')

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Invoiced</div><div class="fs-4 fw-bold">₱{{ number_format($report['invoiced'], 2) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Collected</div><div class="fs-4 fw-bold text-success">₱{{ number_format($report['collected'], 2) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Outstanding (open)</div><div class="fs-4 fw-bold text-danger">₱{{ number_format($report['outstanding'], 2) }}</div></div></div>
        <div class="col-6 col-md-3"><div class="da-stat-card"><div class="text-muted small">Collection rate</div><div class="fs-4 fw-bold">{{ $report['collection_rate'] !== null ? $report['collection_rate'].'%' : '—' }}</div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="da-panel p-4 h-100">
                <h2 class="h6 mb-3">Invoiced vs collected</h2>
                <x-reports.bar-chart
                    id="payment-collection-trend"
                    :labels="$report['monthly']->pluck('label')->all()"
                    :datasets="[[
                        'label' => 'Invoiced',
                        'data' => $report['monthly']->pluck('invoiced')->all(),
                        'backgroundColor' => 'rgba(13, 110, 253, 0.6)',
                    ], [
                        'label' => 'Collected',
                        'data' => $report['monthly']->pluck('collected')->all(),
                        'backgroundColor' => 'rgba(25, 135, 84, 0.6)',
                    ]]"
                />
            </div>
        </div>
        <div class="col-lg-5">
            <div class="da-panel p-4 h-100">
                <h2 class="h6 mb-3">Open invoices by status</h2>
                <ul class="list-unstyled mb-0 small">
                    @foreach($report['invoices_by_status'] as $status => $row)
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>{{ \App\Enums\InvoiceStatus::from($status)->label() }}</span>
                            <span>{{ $row['count'] }} · ₱{{ number_format($row['balance'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
