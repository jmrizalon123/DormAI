@extends('layouts.app')
@section('title', 'New work order')
@section('page-title', 'New work order')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.work-orders.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Property</label><select name="property_id" class="form-select" required>@foreach($properties as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Room</label><select name="room_id" class="form-select"><option value="">—</option>@foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->room_number }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select">@foreach(App\Enums\MaintenancePriority::cases() as $p)<option value="{{ $p->value }}">{{ $p->label() }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Scheduled for</label><input type="date" name="scheduled_for" class="form-control"></div>
                <div class="col-12"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Create</button>
        </form>
    </div>
@endsection
