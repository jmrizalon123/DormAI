@extends('layouts.app')
@section('page-title', 'Add room')
@section('page-subtitle', $floor->building->name.' · '.$floor->name)
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.floors.rooms.store', $floor) }}">@csrf
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Room number</label><input name="room_number" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Capacity (beds)</label><input type="number" name="capacity" class="form-control" min="1" value="4" required></div>
                <div class="col-md-4"><label class="form-label">Monthly rate (₱)</label><input type="number" step="0.01" name="monthly_rate" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Room type</label><select name="room_type_id" class="form-select"><option value="">—</option>@foreach($roomTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(App\Enums\RoomStatus::cases() as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                @if($amenities->isNotEmpty())
                    <div class="col-12"><label class="form-label">Amenities</label><div class="d-flex flex-wrap gap-2">@foreach($amenities as $amenity)<label class="border rounded px-2 py-1"><input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"> {{ $amenity->name }}</label>@endforeach</div></div>
                @endif
            </div>
            <p class="text-muted small mt-3 mb-0">Bed spaces A–{{ chr(64 + min(4, 26)) }} (based on capacity) will be created automatically.</p>
            <button class="btn btn-primary mt-3">Create room</button>
        </form>
    </div></div>
@endsection
