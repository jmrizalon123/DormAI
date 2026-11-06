@extends('layouts.marketplace')

@section('title', $message->reference.' — Contact — '.config('app.name'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="da-panel p-4 p-lg-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                        <div>
                            <h1 class="h5 fw-bold mb-1">{{ $message->subject }}</h1>
                            <p class="text-muted small mb-0">Reference {{ $message->reference }} · {{ $message->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                        <span class="badge text-bg-{{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span>
                    </div>

                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="small text-muted mb-1">{{ $message->name }} &lt;{{ $message->email }}&gt;</div>
                        <div class="small" style="white-space: pre-wrap;">{{ $message->body }}</div>
                    </div>

                    @if($message->replies->isNotEmpty())
                        <h2 class="h6 text-uppercase text-muted mb-3">Replies from {{ config('app.name') }}</h2>
                        <div class="vstack gap-3 mb-4">
                            @foreach($message->replies->sortBy('created_at') as $reply)
                                <div class="border rounded p-3 border-success border-opacity-25">
                                    <div class="small text-muted mb-1">{{ $reply->created_at->format('M j, Y g:i A') }} · Platform team</div>
                                    <div class="small" style="white-space: pre-wrap;">{{ $reply->body }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">No reply yet. We will email you at {{ $message->email }} when the team responds.</p>
                    @endif

                    <a href="{{ route('public.contact.create') }}" class="btn btn-outline-primary btn-sm">Send another message</a>
                    <a href="{{ route('home') }}" class="btn btn-link btn-sm">Marketplace home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
