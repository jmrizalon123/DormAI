@extends('layouts.guest')

@section('title', 'Confirm password — '.config('app.name'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Confirm password</h1>
            <p class="text-muted small">Please confirm your password to continue.</p>
            <form method="POST" action="{{ route('password.confirm') }}" data-da-no-confirm>
                @csrf
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Confirm</button>
            </form>
        </div>
    </div>
@endsection
