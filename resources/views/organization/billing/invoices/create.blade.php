@extends('layouts.app')
@section('title', 'New invoice')
@section('page-title', 'New invoice')
@section('content')
    <div class="da-panel mx-auto" style="max-width: 640px;"><form method="POST" action="{{ route('organization.invoices.store') }}" class="p-4">@csrf
        <div class="row g-3">
            <div class="col-12"><label class="form-label">Tenant</label><select name="tenant_id" class="form-select" required><option value="">Select tenant</option>@foreach($tenants as $t)<option value="{{ $t->id }}" @selected(old('tenant_id')==$t->id)>{{ $t->fullName() }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Period start</label><input type="date" name="billing_period_start" class="form-control" value="{{ old('billing_period_start', now()->startOfMonth()->toDateString()) }}" required></div>
            <div class="col-md-6"><label class="form-label">Period end</label><input type="date" name="billing_period_end" class="form-control" value="{{ old('billing_period_end', now()->endOfMonth()->toDateString()) }}" required></div>
            <div class="col-12"><label class="form-label">Line item description</label><input type="text" name="description" class="form-control" value="{{ old('description', 'Monthly rent') }}" required></div>
            <div class="col-md-6"><label class="form-label">Amount (₱)</label><input type="number" step="0.01" name="unit_price" class="form-control" required></div>
            <div class="col-12"><div class="form-check"><input type="checkbox" name="issue_now" value="1" class="form-check-input" id="issue_now" checked><label for="issue_now" class="form-check-label">Issue immediately</label></div></div>
        </div>
        <button type="submit" class="btn btn-primary mt-4">Create invoice</button>
    </form></div>
@endsection
