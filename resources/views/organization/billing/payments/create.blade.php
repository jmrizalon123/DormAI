@extends('layouts.app')
@section('title', 'Record payment')
@section('page-title', 'Record payment')
@section('content')
    <div class="da-panel mx-auto" style="max-width: 720px;"><form method="POST" action="{{ route('organization.payments.store') }}" class="p-4">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Tenant</label>
                <select name="tenant_id" class="form-select" required onchange="if(this.value) window.location='{{ route('organization.payments.create') }}?tenant_id='+this.value">
                    <option value="">Select tenant</option>@foreach($tenants as $t)<option value="{{ $t->id }}" @selected(optional($tenant)->id === $t->id)>{{ $t->fullName() }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Payment method</label><select name="payment_method" class="form-select" required>@foreach(App\Enums\PaymentMethod::cases() as $m)<option value="{{ $m->value }}">{{ $m->label() }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Amount (₱)</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Paid on</label><input type="date" name="paid_at" class="form-control" value="{{ now()->toDateString() }}"></div>
            <div class="col-md-6"><label class="form-label">Reference #</label><input type="text" name="reference_number" class="form-control"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        </div>
        @if($tenant && $openInvoices->isNotEmpty())
            <hr class="my-4">
            <h2 class="h6 fw-bold">Allocate to invoices (optional — leave blank for auto FIFO)</h2>
            @foreach($openInvoices as $inv)
                <div class="row g-2 align-items-center mb-2">
                    <div class="col"><span class="small font-monospace">{{ $inv->invoice_number }}</span> · balance ₱{{ number_format($inv->balance, 2) }}</div>
                    <div class="col-md-4"><input type="hidden" name="allocations[{{ $loop->index }}][invoice_id]" value="{{ $inv->id }}"><input type="number" step="0.01" name="allocations[{{ $loop->index }}][amount]" class="form-control form-control-sm" placeholder="Allocate"></div>
                </div>
            @endforeach
        @elseif($tenant)
            <p class="text-muted small mt-3 mb-0">No open invoices — payment will fail unless you create an invoice first.</p>
        @endif
        <button type="submit" class="btn btn-primary mt-4" @disabled(! $tenant)>Save payment</button>
    </form></div>
@endsection
