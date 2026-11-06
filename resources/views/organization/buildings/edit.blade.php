@extends('layouts.app')
@section('page-title', 'Edit building')
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.buildings.update', $building) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Building name</label><input name="name" class="form-control" value="{{ old('name', $building->name) }}" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $building->description) }}</textarea></div>
            <button class="btn btn-primary">Update</button>
        </form>
    </div></div>
@endsection
