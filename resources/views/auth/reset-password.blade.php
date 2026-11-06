@extends('layouts.guest')

@section('title', 'Reset password — '.config('app.name'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Choose a new password</h1>
            <form method="POST" action="{{ route('password.update') }}" data-da-no-confirm>
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $request->email) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update password</button>
            </form>
        </div>
    </div>
@endsection
