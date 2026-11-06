@extends('layouts.app')
@section('page-title', 'Add room type')
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.room-types.store') }}">@csrf
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div></div>
@endsection
