@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('page-title', 'Invoice '.$invoice->invoice_number)
@section('page-actions')
    @if($invoice->balance > 0)
        <a href="{{ route('portal.payment-submissions.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-primary btn-sm">Submit payment</a>
    @endif
@endsection
@section('content')
    <div class="da-panel p-4">
        <div class="d-flex justify-content-between mb-3"><span class="badge text-bg-{{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span><span class="text-muted small">Due {{ $invoice->due_date->format('M j, Y') }}</span></div>
        <table class="table mb-0"><thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
            <tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td class="text-end">₱{{ number_format($item->amount, 2) }}</td></tr>@endforeach</tbody>
            <tfoot><tr><th>Balance due</th><th class="text-end">₱{{ number_format($invoice->balance, 2) }}</th></tr></tfoot>
        </table>
    </div>
@endsection
