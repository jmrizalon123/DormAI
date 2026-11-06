@extends('layouts.app')

@section('title', 'Profile — '.config('app.name'))
@section('page-title', 'Profile settings')

@section('content')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Profile information</h2>
                    <form method="POST" action="{{ route('user-profile-information.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <button class="btn btn-primary">Save profile</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6" id="account-security">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Account security</h2>
                    <p class="small text-muted mb-3">Update your password to keep your account secure.</p>
                    <form method="POST" action="{{ route('user-password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Current password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm new password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button class="btn btn-primary">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
