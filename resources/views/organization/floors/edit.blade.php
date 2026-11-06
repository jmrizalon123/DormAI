@extends('layouts.app')
@section('page-title', 'Edit floor')
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.floors.update', $floor) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Floor name</label><input name="name" class="form-control" value="{{ old('name', $floor->name) }}" required></div>
            <div class="mb-3"><label class="form-label">Level number</label><input type="number" name="level_number" class="form-control" value="{{ old('level_number', $floor->level_number) }}" required></div>
            <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(App\Enums\FloorStatus::cases() as $s)<option value="{{ $s->value }}" @selected($floor->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
            <button class="btn btn-primary">Update</button>
        </form>
    </div></div>
@endsection
