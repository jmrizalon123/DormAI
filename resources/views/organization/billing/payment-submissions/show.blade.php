@extends('layouts.app')
@section('title', 'Payment submission')
@section('page-title', 'Review payment submission')
@section('page-subtitle', $paymentSubmission->tenant->fullName())
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel p-4">
                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Amount</dt><dd class="col-sm-8 fw-bold">₱{{ number_format($paymentSubmission->amount, 2) }}</dd>
                    <dt class="col-sm-4 text-muted">Method</dt><dd class="col-sm-8">{{ $paymentSubmission->payment_method }}</dd>
                    <dt class="col-sm-4 text-muted">Reference</dt><dd class="col-sm-8">{{ $paymentSubmission->reference_number }}</dd>
                    <dt class="col-sm-4 text-muted">Invoice</dt><dd class="col-sm-8">@if($paymentSubmission->invoice)<a href="{{ route('organization.invoices.show', $paymentSubmission->invoice) }}">{{ $paymentSubmission->invoice->invoice_number }}</a>@else—@endif</dd>
                    <dt class="col-sm-4 text-muted">Notes</dt><dd class="col-sm-8">{{ $paymentSubmission->notes ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Status</dt><dd class="col-sm-8"><span class="badge text-bg-{{ $paymentSubmission->status->badgeClass() }}">{{ $paymentSubmission->status->label() }}</span></dd>
                </dl>
            </div>
        </div>
        @if($paymentSubmission->isPending())
            <div class="col-lg-4">
                <form method="POST" action="{{ route('organization.payment-submissions.approve', $paymentSubmission) }}" class="da-panel p-3 mb-3">@csrf<button class="btn btn-success w-100">Approve & record payment</button></form>
                <form method="POST" action="{{ route('organization.payment-submissions.reject', $paymentSubmission) }}" class="da-panel p-3">@csrf
                    <label class="form-label small">Rejection reason</label>
                    <textarea name="reason" class="form-control form-control-sm mb-2" rows="3" required></textarea>
                    <button class="btn btn-outline-danger w-100">Reject</button>
                </form>
            </div>
        @endif
    </div>
@endsection
