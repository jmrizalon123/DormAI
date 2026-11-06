@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('page-title', 'Invoice '.$invoice->invoice_number)
@section('page-subtitle', $invoice->tenant->fullName())
@section('page-actions')
    <a href="{{ route('organization.invoices.print', $invoice) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Print</a>
    @if($invoice->balance > 0)
        <a href="{{ route('organization.payments.create', ['tenant_id' => $invoice->tenant_id]) }}" class="btn btn-primary btn-sm">Record payment</a>
    @endif
@endsection
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel mb-3"><div class="p-4">
                <div class="d-flex justify-content-between mb-3"><span class="badge text-bg-{{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span><span class="text-muted small">Due {{ $invoice->due_date->format('M j, Y') }}</span></div>
                <table class="table mb-0"><thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td class="text-end">₱{{ number_format($item->amount, 2) }}</td></tr>@endforeach</tbody>
                    <tfoot><tr><th>Total</th><th class="text-end">₱{{ number_format($invoice->subtotal, 2) }}</th></tr>
                    <tr><td>Paid</td><td class="text-end">₱{{ number_format($invoice->amount_paid, 2) }}</td></tr>
                    <tr><th>Balance</th><th class="text-end">₱{{ number_format($invoice->balance, 2) }}</th></tr></tfoot>
                </table>
            </div></div>
            @if($invoice->allocations->isNotEmpty())
                <div class="da-panel"><div class="p-4"><h2 class="h6 fw-bold">Payment history</h2>
                    <ul class="list-unstyled mb-0 small">@foreach($invoice->allocations as $alloc)<li class="mb-2"><a href="{{ route('organization.payments.show', $alloc->payment) }}">{{ $alloc->payment->payment_number }}</a> — ₱{{ number_format($alloc->amount, 2) }} · {{ $alloc->payment->paid_at->format('M j, Y') }}</li>@endforeach</ul>
                </div></div>
            @endif
        </div>
        <div class="col-lg-4">
            @if($invoice->status === App\Enums\InvoiceStatus::Draft)
                <form method="POST" action="{{ route('organization.invoices.issue', $invoice) }}" class="mb-2">@csrf<button class="btn btn-success w-100">Issue invoice</button></form>
            @endif
            @if(! in_array($invoice->status, [App\Enums\InvoiceStatus::Void, App\Enums\InvoiceStatus::Paid]) && $invoice->amount_paid == 0)
                <form method="POST" action="{{ route('organization.invoices.void', $invoice) }}">@csrf<button type="submit" class="btn btn-outline-danger w-100">Void</button></form>
            @endif
        </div>
    </div>
@endsection
