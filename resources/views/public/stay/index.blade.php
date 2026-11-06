@extends('layouts.marketplace')

@section('title', $organization->name.' — Available beds')

@section('content')
    <div class="mx-auto" style="max-width: 720px;">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-1">{{ $organization->name }}</h1>
            <p class="text-muted mb-0">Available bed spaces — submit a reservation request online</p>
        </div>

        @if($beds->isEmpty())
            <div class="alert alert-secondary border-0 text-center">No beds are available for booking right now.</div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach($beds as $bed)
                    @php($room = $bed->room)
                    <div class="da-public-listing bg-white p-3 p-md-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <div class="fw-bold">{{ $room->property->name }}</div>
                                <div class="text-muted small">Room {{ $room->room_number }} · {{ $bed->label }}</div>
                                <div class="mt-2 fw-semibold text-primary">₱{{ number_format($bed->monthly_rate ?? $room->monthly_rate, 2) }} <span class="text-muted fw-normal small">/ month</span></div>
                            </div>
                            <a class="btn btn-primary" href="{{ route('public.stay.show', ['publicOrganization' => $organization->slug, 'bedSpace' => $bed]) }}">Reserve</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
