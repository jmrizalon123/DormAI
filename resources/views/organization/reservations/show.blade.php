@extends('layouts.app')
@section('title', $reservation->reference_code)
@section('page-title', 'Reservation '.$reservation->reference_code)
@section('page-subtitle', $reservation->applicantFullName())
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel mb-3"><div class="p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Status</dt><dd class="col-sm-8"><span class="badge text-bg-{{ $reservation->status->badgeClass() }}">{{ $reservation->status->label() }}</span></dd>
                    <dt class="col-sm-4 text-muted">Bed</dt><dd class="col-sm-8">{{ $reservation->property->name }} · Room {{ $reservation->room->room_number }} · {{ $reservation->bedSpace->label }}</dd>
                    <dt class="col-sm-4 text-muted">Email / phone</dt><dd class="col-sm-8">{{ $reservation->applicant_email }} · {{ $reservation->applicant_phone ?: '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Check-in</dt><dd class="col-sm-8">{{ $reservation->requested_check_in->format('M j, Y') }}</dd>
                    <dt class="col-sm-4 text-muted">Check-out</dt><dd class="col-sm-8">{{ $reservation->requested_check_out?->format('M j, Y') ?: '—' }}</dd>
                    @if($reservation->expires_at && $reservation->status === App\Enums\ReservationStatus::Pending)
                        <dt class="col-sm-4 text-muted">Expires</dt><dd class="col-sm-8">{{ $reservation->expires_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</dd>
                    @endif
                    @if($reservation->deposit_amount)
                        <dt class="col-sm-4 text-muted">Deposit</dt>
                        <dd class="col-sm-8">
                            ₱{{ number_format($reservation->deposit_amount, 2) }}
                            @if($reservation->deposit_paid_at)
                                <span class="badge text-bg-success ms-1">Paid {{ $reservation->deposit_paid_at->format('M j, Y') }}</span>
                            @else
                                <span class="badge text-bg-warning ms-1">Unpaid</span>
                            @endif
                        </dd>
                    @endif
                    @if($reservation->applicant_notes)<dt class="col-sm-4 text-muted">Guest notes</dt><dd class="col-sm-8">{{ $reservation->applicant_notes }}</dd>@endif
                    @if($reservation->rejection_reason)<dt class="col-sm-4 text-muted">Rejection</dt><dd class="col-sm-8 text-danger">{{ $reservation->rejection_reason }}</dd>@endif
                </dl>
            </div></div>

            <div class="da-panel mb-3"><div class="p-4">
                <h2 class="h6 fw-bold mb-3">Staff notes</h2>
                <form method="POST" action="{{ route('organization.reservations.staff-notes', $reservation) }}">
                    @csrf
                    @method('PUT')
                    <textarea name="staff_notes" class="form-control mb-2" rows="3" placeholder="Internal notes for your team">{{ old('staff_notes', $reservation->staff_notes) }}</textarea>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save notes</button>
                </form>
            </div></div>

            @if($reservation->contract)
                <a href="{{ route('organization.contracts.show', $reservation->contract) }}" class="btn btn-outline-primary">View contract</a>
            @endif
            @if($reservation->tenant)
                <a href="{{ route('organization.tenants.show', $reservation->tenant) }}" class="btn btn-outline-secondary ms-2">View tenant</a>
            @endif
        </div>
        <div class="col-lg-4">
            @if($reservation->status === App\Enums\ReservationStatus::Pending)
                <div class="da-panel mb-3"><div class="p-4 d-grid gap-2">
                    <form method="POST" action="{{ route('organization.reservations.approve', $reservation) }}">@csrf<button type="submit" class="btn btn-success">Approve</button></form>
                    <form method="POST" action="{{ route('organization.reservations.reject', $reservation) }}">@csrf
                        <label class="form-label small fw-semibold">Rejection reason</label>
                        <textarea name="rejection_reason" class="form-control mb-2" rows="2" required></textarea>
                        <button type="submit" class="btn btn-outline-danger w-100">Reject</button>
                    </form>
                    <form method="POST" action="{{ route('organization.reservations.cancel', $reservation) }}">@csrf<button type="submit" class="btn btn-link text-muted">Cancel request</button></form>
                </div></div>
            @endif
            @if($reservation->status === App\Enums\ReservationStatus::Approved)
                <div class="da-panel mb-3"><div class="p-4">
                    @if($reservation->deposit_amount && ! $reservation->deposit_paid_at)
                        <div class="alert alert-warning small py-2 mb-3">Deposit must be marked paid before check-in.</div>
                        <form method="POST" action="{{ route('organization.reservations.deposit-paid', $reservation) }}" class="mb-3">@csrf<button type="submit" class="btn btn-outline-secondary w-100">Mark deposit paid</button></form>
                    @endif
                    <form method="POST" action="{{ route('organization.reservations.check-in', $reservation) }}">@csrf
                        <label class="form-label small fw-semibold">Link existing tenant (optional)</label>
                        <select name="tenant_id" class="form-select mb-2"><option value="">Create new tenant from applicant</option>@foreach($tenants as $t)<option value="{{ $t->id }}">{{ $t->fullName() }}</option>@endforeach</select>
                        <label class="form-label small fw-semibold">Monthly rate override (optional)</label>
                        <input type="number" step="0.01" name="monthly_rate" class="form-control mb-3" placeholder="{{ $reservation->bedSpace->monthly_rate ?? $reservation->room->monthly_rate }}">
                        <button type="submit" class="btn btn-primary w-100" @disabled($reservation->deposit_amount && ! $reservation->deposit_paid_at)>Check in &amp; create contract</button>
                    </form>
                </div></div>
            @endif
        </div>
    </div>
@endsection
