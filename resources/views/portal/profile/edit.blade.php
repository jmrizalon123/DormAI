@extends('layouts.app')
@section('title', 'My profile')
@section('page-title', 'My profile')
@section('page-subtitle', $tenant->fullName())
@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('portal.profile.update') }}" class="da-panel p-4">@csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $tenant->phone) }}"></div>
                    <div class="col-md-6"><label class="form-label">School / work</label><input type="text" name="school_or_work" class="form-control" value="{{ old('school_or_work', $tenant->school_or_work) }}"></div>
                    <div class="col-12"><label class="form-label">Address</label><input type="text" name="address_line1" class="form-control" value="{{ old('address_line1', $tenant->address_line1) }}"></div>
                    <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $tenant->city) }}"></div>
                    <div class="col-md-6"><label class="form-label">Province</label><input type="text" name="province" class="form-control" value="{{ old('province', $tenant->province) }}"></div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Save changes</button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="da-panel p-4 small">
                <dl class="mb-0">
                    <dt class="text-muted">Email</dt><dd>{{ $tenant->email ?? auth()->user()->email }}</dd>
                    <dt class="text-muted">Status</dt><dd>{{ $tenant->status->label() }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
