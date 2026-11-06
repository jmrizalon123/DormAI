@extends('layouts.guest')

@section('title', 'Verify email — '.config('app.name'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Verify your email</h1>
            <p class="text-muted">Please verify your email address before continuing. Check your inbox for the verification link.</p>
            <form method="POST" action="{{ route('verification.send') }}" class="mt-3" data-da-no-confirm>
                @csrf
                <button type="submit" class="btn btn-outline-primary">Resend verification email</button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="mt-3" data-da-confirm="Sign out of your account?" data-da-confirm-title="Sign out">
                @csrf
                <button type="submit" class="btn btn-link px-0">Sign out</button>
            </form>
        </div>
    </div>
@endsection
