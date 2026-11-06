@extends('layouts.marketplace')

@section('title', 'Reserve '.$bedSpace->label)

@section('content')
    <div class="mx-auto" style="max-width: 560px;">
        <p class="mb-3"><a href="{{ route('public.stay.index', ['publicOrganization' => $organization->slug]) }}" class="text-decoration-none fw-medium"><i class="bi bi-arrow-left me-1"></i>All listings</a></p>
        <h1 class="h3 fw-bold mb-2">Reservation request</h1>
        <p class="text-muted mb-4">
            {{ $bedSpace->room->property->name }} · Room {{ $bedSpace->room->room_number }} · {{ $bedSpace->label }}
        </p>

        <div class="card da-auth-card border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('public.stay.reserve', ['publicOrganization' => $organization->slug, 'bedSpace' => $bedSpace]) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">First name</label>
                            <input type="text" name="applicant_first_name" class="form-control" value="{{ old('applicant_first_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Last name</label>
                            <input type="text" name="applicant_last_name" class="form-control" value="{{ old('applicant_last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="applicant_email" class="form-control" value="{{ old('applicant_email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="applicant_phone" class="form-control" value="{{ old('applicant_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Requested check-in</label>
                            <input type="date" name="requested_check_in" class="form-control" value="{{ old('requested_check_in') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Check-out (optional)</label>
                            <input type="date" name="requested_check_out" class="form-control" value="{{ old('requested_check_out') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notes</label>
                            <textarea name="applicant_notes" class="form-control" rows="3">{{ old('applicant_notes') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-4 py-2">Submit request</button>
                </form>
            </div>
        </div>
    </div>
@endsection
