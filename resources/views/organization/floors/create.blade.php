@extends('layouts.app')
@section('page-title', 'Add floor')
@section('page-subtitle', $building->name)
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.buildings.floors.store', $building) }}">@csrf
            <div class="mb-3"><label class="form-label">Floor name</label><input name="name" class="form-control" placeholder="Floor 2" required></div>
            <div class="mb-3"><label class="form-label">Level number</label><input type="number" name="level_number" class="form-control" min="0" value="2" required></div>
            <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <button class="btn btn-primary">Save floor</button>
        </form>
    </div></div>
@endsection
