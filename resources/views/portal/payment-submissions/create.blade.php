@extends('layouts.app')
@section('title', 'Submit payment')
@section('page-title', 'Submit payment reference')
@section('content')
    <form method="POST" action="{{ route('portal.payment-submissions.store') }}" enctype="multipart/form-data" class="da-panel p-4 col-lg-8">@csrf
        <div class="mb-3">
            <label class="form-label">Apply to invoice (optional)</label>
            <select name="invoice_id" class="form-select">
                <option value="">General payment</option>
                @foreach($openInvoices as $invoice)
                    <option value="{{ $invoice->id }}" @selected(old('invoice_id', request('invoice_id')) == $invoice->id)>{{ $invoice->invoice_number }} — balance ₱{{ number_format($invoice->balance, 2) }}</option>
                @endforeach
            </select>
        </div>
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Amount (₱)</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required></div>
            <div class="col-md-4"><label class="form-label">Method</label><select name="payment_method" class="form-select" required>@foreach([App\Enums\PaymentMethod::Gcash, App\Enums\PaymentMethod::Paymaya, App\Enums\PaymentMethod::BankTransfer, App\Enums\PaymentMethod::Other] as $m)<option value="{{ $m->value }}" @selected(old('payment_method')===$m->value)>{{ $m->label() }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Reference no.</label><input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" required></div>
        </div>
        <div class="mt-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
        <div class="mt-3"><label class="form-label">Proof (optional)</label><input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf"></div>
        <button type="submit" class="btn btn-primary mt-3">Submit for review</button>
    </form>
@endsection
