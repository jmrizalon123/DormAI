@extends('layouts.app')
@section('page-title', 'Edit room '.$room->room_number)
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.rooms.update', $room) }}">@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Room number</label><input name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required></div>
                <div class="col-md-4"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}" min="{{ $room->bedSpaces->count() }}" required></div>
                <div class="col-md-4"><label class="form-label">Monthly rate</label><input type="number" step="0.01" name="monthly_rate" class="form-control" value="{{ old('monthly_rate', $room->monthly_rate) }}" required></div>
                <div class="col-md-6"><label class="form-label">Room type</label><select name="room_type_id" class="form-select"><option value="">—</option>@foreach($roomTypes as $type)<option value="{{ $type->id }}" @selected((int)$room->room_type_id === $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(App\Enums\RoomStatus::cases() as $s)<option value="{{ $s->value }}" @selected($room->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $room->notes) }}</textarea></div>
                @if($amenities->isNotEmpty())
                    <div class="col-12"><label class="form-label">Amenities</label><div class="d-flex flex-wrap gap-2">@foreach($amenities as $amenity)<label class="border rounded px-2 py-1"><input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" @checked($room->amenities->contains($amenity))> {{ $amenity->name }}</label>@endforeach</div></div>
                @endif
            </div>
            <button class="btn btn-primary mt-3">Save changes</button>
        </form>
    </div></div>
@endsection
