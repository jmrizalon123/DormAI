@extends('layouts.app')
@section('title', 'Billing settings')
@section('page-title', 'Billing settings')
@section('page-subtitle', 'Due dates, invoice numbering, and automation')
@section('content')
    <div class="da-panel mx-auto" style="max-width: 640px;">
        <form method="POST" action="{{ route('organization.billing.settings.update') }}" class="p-4">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Due day of month</label>
                    <input type="number" name="due_day" min="1" max="28" class="form-control" value="{{ old('due_day', $billing['due_day']) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Grace days after due</label>
                    <input type="number" name="grace_days" min="0" max="30" class="form-control" value="{{ old('grace_days', $billing['grace_days']) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Invoice prefix</label>
                    <input type="text" name="invoice_prefix" maxlength="10" class="form-control" value="{{ old('invoice_prefix', $billing['invoice_prefix']) }}" required>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="auto_generate_monthly" value="1" class="form-check-input" id="auto_generate_monthly" @checked(old('auto_generate_monthly', $billing['auto_generate_monthly']))>
                        <label class="form-check-label" for="auto_generate_monthly">Automatically generate monthly rent invoices (scheduler)</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Save settings</button>
        </form>
    </div>
@endsection
