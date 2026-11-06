@extends('layouts.app')
@section('title', 'Log maintenance request')
@section('page-title', 'Log tenant maintenance request')
@section('content')
    <div class="da-panel p-4">
        <form method="POST" action="{{ route('organization.maintenance-requests.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Tenant (optional)</label><select name="tenant_id" class="form-select"><option value="">—</option>@foreach($tenants as $t)<option value="{{ $t->id }}" @selected(old('tenant_id')==$t->id)>{{ $t->fullName() }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Property</label><select name="property_id" class="form-select" required>@foreach($properties as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Room (optional)</label><select name="room_id" class="form-select"><option value="">—</option>@foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->room_number }} · {{ $r->property->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select">@foreach(App\Enums\MaintenancePriority::cases() as $p)<option value="{{ $p->value }}">{{ $p->label() }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" required></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Submit request</button>
        </form>
    </div>
@endsection
