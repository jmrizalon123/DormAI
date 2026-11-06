@extends('layouts.app')
@section('page-title', 'Edit room type')
@section('content')
    <div class="card border-0 shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('organization.room-types.update', $roomType) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ $roomType->name }}" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ $roomType->description }}</textarea></div>
            <button class="btn btn-primary">Update</button>
        </form>
    </div></div>
@endsection
