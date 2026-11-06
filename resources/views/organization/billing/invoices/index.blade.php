@extends('layouts.app')
@section('title', 'Invoices')
@section('page-title', 'Invoices')
@section('page-subtitle', 'Outstanding balance: ₱'.number_format($outstanding, 2))
@section('page-actions')
    <a href="{{ route('organization.invoices.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New invoice</a>
@endsection
@section('content')
    <div class="da-filter-bar">
        <form method="GET" class="row g-3 da-filter-row">
            <div class="col-md-5"><label class="form-label">Search</label><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Invoice # or tenant"></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(App\Enums\InvoiceStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div class="col-md-3 da-filter-submit"><button type="submit" class="btn btn-primary w-100">Apply filters</button></div>
        </form>
    </div>
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Invoice</th><th>Tenant</th><th>Period</th><th>Due</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <x-da-table-num-td :paginator="$invoices" :row="$loop->index" />
                            <td class="font-monospace small fw-semibold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->tenant->fullName() }}</td>
                            <td class="small text-muted">{{ $invoice->billing_period_start->format('M Y') }}</td>
                            <td>{{ $invoice->due_date->format('M j, Y') }}</td>
                            <td class="fw-semibold">₱{{ number_format($invoice->balance, 2) }}</td>
                            <td><span class="badge text-bg-{{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="da-empty-state"><i class="bi bi-receipt"></i>No invoices yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$invoices" />
    </div>
@endsection
