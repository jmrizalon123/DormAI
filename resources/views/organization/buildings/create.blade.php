@extends('layouts.app')
@section('page-title', 'Add building')
@section('page-subtitle', $property->name)
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.properties.buildings.store', $property) }}">@csrf
            <div class="mb-3"><label class="form-label">Building name</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('organization.properties.show', $property) }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div></div>
@endsection
