@extends('layouts.app')
@section('title', $contract->contract_number)
@section('page-title', 'Contract '.$contract->contract_number)
@section('page-subtitle', $contract->tenant->fullName())
@section('page-actions')
    <a href="{{ route('organization.contracts.print', $contract) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Print</a>
    <a href="{{ route('organization.contracts.download', $contract) }}" class="btn btn-outline-secondary btn-sm">Download HTML</a>
@endsection
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $contract->terms }}</pre>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <dl class="row mb-3 small">
                    <dt class="col-5">Monthly rate</dt><dd class="col-7">₱{{ number_format($contract->monthly_rate, 2) }}</dd>
                    <dt class="col-5">Security deposit</dt><dd class="col-7">₱{{ number_format($contract->security_deposit, 2) }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">{{ $contract->status->label() }}</dd>
                </dl>
                @if($contract->status === App\Enums\ContractStatus::Active)
                    <form method="POST" action="{{ route('organization.contracts.renew', $contract) }}">@csrf
                        <label class="form-label small">Renew until</label>
                        <input type="date" name="end_date" class="form-control mb-2" required>
                        <label class="form-label small">New monthly rate (optional)</label>
                        <input type="number" step="0.01" name="monthly_rate" class="form-control mb-2">
                        <button class="btn btn-primary w-100">Renew contract</button>
                    </form>
                @endif
            </div></div>
        </div>
    </div>
@endsection
