@extends('layouts.marketplace')

@section('title', 'Message sent — '.config('app.name'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="da-panel p-4 p-lg-5 text-center">
                    <div class="display-6 text-success mb-3"><i class="bi bi-check-circle" aria-hidden="true"></i></div>
                    <h1 class="h4 fw-bold mb-2">Message received</h1>
                    <p class="text-muted mb-3">Reference <strong>{{ $message->reference }}</strong>. We sent your note to the platform team and will reply to <strong>{{ $message->email }}</strong>.</p>
                    <a href="{{ route('public.contact.show', ['reference' => $message->reference, 'email' => $message->email]) }}" class="btn btn-outline-primary btn-sm">View this thread</a>
                    <a href="{{ route('home') }}" class="btn btn-link btn-sm">Back to marketplace</a>
                </div>
            </div>
        </div>
    </div>
@endsection
