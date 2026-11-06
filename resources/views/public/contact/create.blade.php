@extends('layouts.marketplace')

@section('title', 'Contact us — '.config('app.name'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="da-panel p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2">Contact us</h1>
                    <p class="text-muted mb-4">
                        @if(isset($listingProperty) && $listingProperty)
                            You’re sending a message about <strong>{{ $listingProperty->name }}</strong> through <strong>Contact us</strong>. The listing operator will see it in their admin under <strong>Listing messages</strong> and reply to you <strong>by email</strong> (not live chat). Platform staff may also help if needed.
                        @else
                            Questions about listings, partnerships, or the platform? Use this form — if you opened it from a listing, the message is routed to that listing’s operator; otherwise {{ config('app.name') }} handles it. All replies are sent by email.
                        @endif
                    </p>

                    <form method="POST" action="{{ route('public.contact.store') }}" data-da-no-confirm>
                        @csrf
                        @if(isset($prefill['property_id']) && $prefill['property_id'])
                            <input type="hidden" name="property_id" value="{{ $prefill['property_id'] }}">
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="contactName">Your name</label>
                                <input type="text" name="name" id="contactName" class="form-control @error('name') is-invalid @enderror" value="{{ $prefill['name'] }}" required maxlength="120">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contactEmail">Email</label>
                                <input type="email" name="email" id="contactEmail" class="form-control @error('email') is-invalid @enderror" value="{{ $prefill['email'] }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contactPhone">Phone <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" name="phone" id="contactPhone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $prefill['phone']) }}" maxlength="40">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contactSubject">Subject</label>
                                <input type="text" name="subject" id="contactSubject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', $prefill['subject'] ?? '') }}" required maxlength="160" placeholder="Listing question, support, partnership…">
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="contactBody">Message</label>
                                <textarea name="body" id="contactBody" rows="6" class="form-control @error('body') is-invalid @enderror" required maxlength="5000">{{ old('body', $prefill['body'] ?? '') }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Send message</button>
                            <a href="{{ route('public.contact.track') }}" class="btn btn-outline-secondary">Track a message</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
