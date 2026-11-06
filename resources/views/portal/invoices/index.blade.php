@extends('layouts.app')
@section('title', 'My invoices')
@section('page-title', 'Invoices')
@section('content')
    <div class="da-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><x-da-table-num-th /><th>Invoice</th><th>Period</th><th>Due</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <x-da-table-num-td :paginator="$invoices" :row="$loop->index" />
                            <td class="font-monospace small">{{ $invoice->invoice_number }}</td>
                            <td class="small">{{ $invoice->billing_period_start->format('M j') }} – {{ $invoice->billing_period_end->format('M j, Y') }}</td>
                            <td class="small">{{ $invoice->due_date->format('M j, Y') }}</td>
                            <td class="fw-semibold">₱{{ number_format($invoice->balance, 2) }}</td>
                            <td><span class="badge text-bg-{{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('portal.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="da-empty-state">No invoices yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$invoices" />
    </div>
@endsection
