@extends('layouts.app')

@section('title', $message->reference.' — Listing messages — '.config('app.name'))
@section('page-title', 'Listing messages')
@section('page-subtitle', $message->reference)

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="da-panel p-4 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">{{ $message->subject }}</h2>
                        <p class="text-muted small mb-0">Received {{ $message->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <span class="badge text-bg-{{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span>
                </div>

                <dl class="row small mb-3">
                    <dt class="col-sm-3 text-muted">From</dt>
                    <dd class="col-sm-9">{{ $message->name }} &lt;{{ $message->email }}&gt;</dd>
                    @if(filled($message->phone))
                        <dt class="col-sm-3 text-muted">Phone</dt>
                        <dd class="col-sm-9">{{ $message->phone }}</dd>
                    @endif
                    @if($message->property)
                        <dt class="col-sm-3 text-muted">Listing</dt>
                        <dd class="col-sm-9">
                            <a href="{{ route('public.listings.show', $message->property) }}" target="_blank" rel="noopener">{{ $message->property->name }}</a>
                        </dd>
                    @endif
                </dl>

                <div class="border rounded p-3 bg-light mb-0">
                    <div class="small fw-semibold text-muted mb-2">Message</div>
                    <div style="white-space: pre-wrap;">{{ $message->body }}</div>
                </div>
            </div>

            @if($message->replies->isNotEmpty())
                <div class="da-panel p-4 mb-3">
                    <h3 class="h6 mb-3">Replies</h3>
                    <div class="vstack gap-3">
                        @foreach($message->replies->sortBy('created_at') as $reply)
                            <div class="border rounded p-3">
                                <div class="small text-muted mb-1">
                                    {{ $reply->author->name ?? 'Team' }} · {{ $reply->created_at->format('M j, Y g:i A') }}
                                </div>
                                <div style="white-space: pre-wrap;">{{ $reply->body }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($message->isOpen())
                <div class="da-panel p-4">
                    <h3 class="h6 mb-3">Send reply</h3>
                    <p class="small text-muted">Your reply is emailed to {{ $message->email }} and shown on their tracking page.</p>
                    <form method="POST" action="{{ route('organization.contact-messages.reply', $message) }}" data-da-no-confirm>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="replyBody">Message</label>
                            <textarea name="body" id="replyBody" rows="6" class="form-control @error('body') is-invalid @enderror" required>{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $errors->first('body') }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Send reply</button>
                    </form>
                </div>
            @else
                <p class="text-muted small">This conversation is closed.</p>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="da-panel p-4">
                <h3 class="h6 mb-3">Actions</h3>
                <div class="d-grid gap-2">
                    <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}" class="btn btn-outline-secondary btn-sm">Open in email client</a>
                    @if($message->isOpen())
                        <form method="POST" action="{{ route('organization.contact-messages.close', $message) }}" data-da-confirm="Mark this conversation as closed?" data-da-confirm-title="Close conversation">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Mark closed</button>
                        </form>
                    @endif
                    <a href="{{ route('organization.contact-messages.index') }}" class="btn btn-link btn-sm">Back to inbox</a>
                </div>
            </div>
        </div>
    </div>
@endsection
