@extends('layouts.guest')

@section('title', 'Forgot password — '.config('app.name'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Reset password</h1>
            <p class="text-muted small">Enter your email and we will send a password reset link.</p>
            <form method="POST" action="{{ route('password.email') }}" data-da-no-confirm>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Email reset link</button>
            </form>
            <p class="text-muted small mt-3 mb-0"><a href="{{ route('login') }}">Back to sign in</a></p>
        </div>
    </div>
@endsection
