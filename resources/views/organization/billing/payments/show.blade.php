@extends('layouts.app')
@section('title', $payment->payment_number)
@section('page-title', 'Payment '.$payment->payment_number)
@section('page-subtitle', $payment->tenant->fullName())
@section('page-actions')<a href="{{ route('organization.payments.receipt', $payment) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Print receipt</a>@endsection
@section('content')
    <div class="da-panel p-4" style="max-width:640px">
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Amount</dt><dd class="col-sm-8 fw-bold">₱{{ number_format($payment->amount, 2) }}</dd>
            <dt class="col-sm-4 text-muted">Method</dt><dd class="col-sm-8">{{ $payment->payment_method->label() }}</dd>
            <dt class="col-sm-4 text-muted">Paid on</dt><dd class="col-sm-8">{{ $payment->paid_at->format('M j, Y g:i A') }}</dd>
            <dt class="col-sm-4 text-muted">Recorded by</dt><dd class="col-sm-8">{{ $payment->recordedBy?->name ?? '—' }}</dd>
        </dl>
        @if($payment->allocations->isNotEmpty())
            <hr><h2 class="h6 fw-bold">Allocations</h2>
            <ul class="mb-0">@foreach($payment->allocations as $a)<li><a href="{{ route('organization.invoices.show', $a->invoice) }}">{{ $a->invoice->invoice_number }}</a> — ₱{{ number_format($a->amount, 2) }}</li>@endforeach</ul>
        @endif
    </div>
@endsection
