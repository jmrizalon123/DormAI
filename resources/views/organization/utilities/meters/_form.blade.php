@php $meter = $meter ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Property</label><select name="property_id" class="form-select" required>@foreach($properties as $p)<option value="{{ $p->id }}" @selected(old('property_id', $meter?->property_id)==$p->id)>{{ $p->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Utility type</label><select name="utility_type" class="form-select">@foreach($types as $t)<option value="{{ $t->value }}" @selected(old('utility_type', $meter?->utility_type?->value)===$t->value)>{{ $t->label() }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Label</label><input type="text" name="label" class="form-control" value="{{ old('label', $meter?->label) }}" required></div>
    <div class="col-md-6"><label class="form-label">Meter number</label><input type="text" name="meter_number" class="form-control" value="{{ old('meter_number', $meter?->meter_number) }}"></div>
    <div class="col-md-6"><label class="form-label">Room (optional)</label><select name="room_id" class="form-select"><option value="">Property / shared meter</option>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected(old('room_id', $meter?->room_id)==$room->id)>{{ $room->room_number }} · {{ $room->property->name ?? '' }}</option>@endforeach</select></div>
    <div class="col-md-6 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_shared" value="1" id="is_shared" @checked(old('is_shared', $meter?->is_shared))><label class="form-check-label" for="is_shared">Shared meter (split across occupied rooms)</label></div></div>
</div>
