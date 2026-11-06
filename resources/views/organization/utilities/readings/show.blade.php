@extends('layouts.app')
@section('title', 'Reading details')
@section('page-title', 'Utility reading')
@section('page-subtitle', $reading->meter->label.' · '.$reading->reading_date->format('M j, Y'))
@section('page-actions')
    @can('allocate', $reading)
        <form method="POST" action="{{ route('organization.utility-readings.allocate', $reading) }}" class="d-inline">@csrf<button class="btn btn-outline-primary btn-sm">Calculate allocation</button></form>
    @endcan
    @can('bill', $reading)
        <form method="POST" action="{{ route('organization.utility-readings.bill', $reading) }}" class="d-inline">@csrf<button class="btn btn-primary btn-sm">Create draft invoices</button></form>
    @endcan
@endsection
@section('content')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="da-panel p-4">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Property</dt><dd class="col-7">{{ $reading->meter->property->name }}</dd>
                    <dt class="col-5 text-muted">Type</dt><dd class="col-7">{{ $reading->meter->utility_type->label() }}</dd>
                    <dt class="col-5 text-muted">Previous</dt><dd class="col-7">{{ number_format($reading->previous_reading, 3) }}</dd>
                    <dt class="col-5 text-muted">Current</dt><dd class="col-7">{{ number_format($reading->current_reading, 3) }}</dd>
                    <dt class="col-5 text-muted">Consumption</dt><dd class="col-7 fw-bold">{{ number_format($reading->consumption, 3) }}</dd>
                    <dt class="col-5 text-muted">Recorded by</dt><dd class="col-7">{{ $reading->recorder->name }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="da-panel">
                <div class="p-3 border-bottom"><h2 class="h6 mb-0">Room allocations</h2></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Room</th><th>Tenant</th><th>Units</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($reading->allocations as $row)
                                <tr>
                                    <td>{{ $row->room->room_number }}</td>
                                    <td>{{ $row->tenant?->fullName() ?? '—' }}</td>
                                    <td>{{ number_format($row->consumption_units, 3) }}</td>
                                    <td>₱{{ number_format($row->amount, 2) }}</td>
                                    <td>{{ $row->status->label() }}@if($row->invoice_id)<div class="small"><a href="{{ route('organization.invoices.show', $row->invoice_id) }}">Invoice</a></div>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted p-3">Run allocation to split this reading across rooms.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
