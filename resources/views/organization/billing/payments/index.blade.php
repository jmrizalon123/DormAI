@extends('layouts.app')
@section('title', 'Payments')
@section('page-title', 'Payments')
@section('page-subtitle', 'Recorded collections and receipts')
@section('page-actions')<a href="{{ route('organization.payments.create') }}" class="btn btn-primary btn-sm">Record payment</a>@endsection
@section('content')
    <div class="da-filter-bar"><form method="GET" class="row g-3 da-filter-row">
        <div class="col-md-4"><label class="form-label">Tenant</label><select name="tenant_id" class="form-select"><option value="">All</option>@foreach($tenants as $t)<option value="{{ $t->id }}" @selected(request('tenant_id')==$t->id)>{{ $t->fullName() }}</option>@endforeach</select></div>
        <div class="col-md-3 da-filter-submit"><button class="btn btn-primary w-100">Apply</button></div>
    </form></div>
    <div class="da-panel"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><x-da-table-num-th /><th>Receipt</th><th>Tenant</th><th>Date</th><th>Method</th><th>Amount</th><th></th></tr></thead>
        <tbody>@forelse($payments as $payment)<tr>
            <x-da-table-num-td :paginator="$payments" :row="$loop->index" />
            <td class="font-monospace small">{{ $payment->payment_number }}</td>
            <td>{{ $payment->tenant->fullName() }}</td>
            <td>{{ $payment->paid_at->format('M j, Y') }}</td>
            <td>{{ $payment->payment_method->label() }}</td>
            <td class="fw-semibold">₱{{ number_format($payment->amount, 2) }}</td>
            <td class="text-end"><a href="{{ route('organization.payments.show', $payment) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
        </tr>@empty<tr><td colspan="7"><div class="da-empty-state">No payments recorded.</div></td></tr>@endforelse</tbody></table></div>
        <x-da-pagination :paginator="$payments" />
    </div>
@endsection
