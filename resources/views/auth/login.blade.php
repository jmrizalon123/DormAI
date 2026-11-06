@extends('layouts.guest')

@section('title', 'Sign in — '.config('app.name'))

@section('content')
    <div class="card da-auth-card border-0">
        <div class="card-body">
            <h1 class="h4 fw-bold mb-1">Sign in</h1>
            <p class="text-muted small mb-4">Access your organization workspace</p>
            <form method="POST" action="{{ route('login') }}" data-da-no-confirm>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small fw-medium">Forgot password?</a>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Sign in</button>
            </form>
            <p class="text-muted small mt-4 mb-0 text-center">
                New organization?
                <a href="{{ route('register') }}" class="fw-semibold">Register your business</a>
            </p>
        </div>
    </div>
@endsection
