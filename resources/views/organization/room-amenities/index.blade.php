@extends('layouts.app')
@section('page-title', 'Room amenities')
@section('content')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <h2 class="h6">Add amenity</h2>
                <form method="POST" action="{{ route('organization.room-amenities.store') }}">@csrf
                    <div class="mb-2"><input name="name" class="form-control" placeholder="Wi-Fi" required></div>
                    <button class="btn btn-primary btn-sm">Add</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <ul class="list-group list-group-flush">
                    @forelse($amenities as $amenity)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="text-muted small me-2">{{ $amenities->firstItem() + $loop->index }}.</span>{{ $amenity->name }}</span>
                            <form method="POST" action="{{ route('organization.room-amenities.destroy', $amenity) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No amenities defined.</li>
                    @endforelse
                </ul>
            </div>
            <x-da-pagination :paginator="$amenities" class="card-footer bg-white border-top" />
        </div>
    </div>
@endsection
