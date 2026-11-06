@extends('layouts.guest')

@section('title', 'Register organization — '.config('app.name'))

@section('content')
    <div class="card da-auth-card border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-1">Register your organization</h1>
            <p class="text-muted small mb-4">Create your owner account and submit your dormitory business for approval.</p>
            <form method="POST" action="{{ route('register.store') }}" data-da-no-confirm>
                @csrf
                <h2 class="h6 text-uppercase text-muted">Organization</h2>
                <div class="mb-3">
                    <label class="form-label" for="organization_name">Business name <span class="text-danger">*</span></label>
                    <input type="text" name="organization_name" id="organization_name" value="{{ old('organization_name') }}" class="form-control" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="organization_email">Business email <span class="text-danger">*</span></label>
                        <input type="email" name="organization_email" id="organization_email" value="{{ old('organization_email') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="organization_phone">Phone</label>
                        <input type="text" name="organization_phone" id="organization_phone" value="{{ old('organization_phone') }}" class="form-control">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label" for="address_line1">Address</label>
                    <input type="text" name="address_line1" id="address_line1" value="{{ old('address_line1') }}" class="form-control">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label" for="city">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="province">Province</label>
                        <input type="text" name="province" id="province" value="{{ old('province') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="postal_code">Postal code</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="form-control">
                    </div>
                </div>

                <h2 class="h6 text-uppercase text-muted">Owner account</h2>
                <div class="mb-3">
                    <label class="form-label" for="owner_name">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name') }}" class="form-control" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="owner_email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="owner_email" id="owner_email" value="{{ old('owner_email') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="owner_phone">Phone</label>
                        <input type="text" name="owner_phone" id="owner_phone" value="{{ old('owner_phone') }}" class="form-control">
                    </div>
                </div>
                <div class="row g-3 mt-0 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Submit registration</button>
            </form>
            <p class="text-muted small mt-3 mb-0">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </div>
@endsection
