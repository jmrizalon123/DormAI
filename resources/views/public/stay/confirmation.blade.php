@extends('layouts.public')

@section('title', 'Reservation submitted')

@section('content')
    <div class="mx-auto" style="max-width: 560px;">
        <div class="alert alert-success border-0 d-flex gap-2 align-items-start">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>Your reservation request was received. The property team will review it shortly.</span>
        </div>
        <div class="card da-auth-card border-0">
            <div class="card-body p-4">
                <p class="text-muted small text-uppercase fw-bold mb-1">Reference</p>
                <h1 class="h4 fw-bold font-monospace mb-3">{{ $reservation->reference_code }}</h1>
                <span class="badge text-bg-{{ $reservation->status->badgeClass() }} mb-4">{{ $reservation->status->label() }}</span>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted">Guest</dt>
                    <dd class="col-sm-8 fw-medium">{{ $reservation->applicantFullName() }}</dd>
                    <dt class="col-sm-4 text-muted">Unit</dt>
                    <dd class="col-sm-8">{{ $reservation->property->name }} · Room {{ $reservation->room->room_number }} · {{ $reservation->bedSpace->label }}</dd>
                    <dt class="col-sm-4 text-muted">Check-in</dt>
                    <dd class="col-sm-8">{{ $reservation->requested_check_in->format('M j, Y') }}</dd>
                </dl>
                <a href="{{ route('public.stay.index', ['publicOrganization' => $organization->slug]) }}" class="btn btn-outline-secondary w-100 mt-4">Back to listings</a>
            </div>
        </div>
    </div>
@endsection
