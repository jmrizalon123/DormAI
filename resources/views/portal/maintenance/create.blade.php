@extends('layouts.app')
@section('title', 'New maintenance request')
@section('page-title', 'Report an issue')
@section('page-subtitle')@if($tenant->activeAssignment){{ $tenant->activeAssignment->property->name }} · Room {{ $tenant->activeAssignment->room->room_number }}@else<span class="text-danger">No active room assignment</span>@endif@endsection
@section('content')
    <form method="POST" action="{{ route('portal.maintenance.store') }}" class="da-panel p-4 col-lg-8">@csrf
        <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="160"></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea></div>
        <div class="mb-3"><label class="form-label">Priority</label><select name="priority" class="form-select">@foreach(App\Enums\MaintenancePriority::cases() as $p)<option value="{{ $p->value }}" @selected(old('priority', 'normal')===$p->value)>{{ $p->label() }}</option>@endforeach</select></div>
        <button type="submit" class="btn btn-primary" @disabled(! $tenant->activeAssignment)>Submit request</button>
    </form>
@endsection
