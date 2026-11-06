@extends('layouts.app')
@section('page-title', $building->name)
@section('page-subtitle', $building->property->name)
@section('page-actions')
    <a href="{{ route('organization.buildings.edit', $building) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    <a href="{{ route('organization.buildings.floors.create', $building) }}" class="btn btn-primary btn-sm">Add floor</a>
@endsection
@section('content')
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><x-da-table-num-th /><th>Floor</th><th>Level</th><th>Rooms</th><th></th></tr></thead>
                <tbody>
                    @forelse ($building->floors as $floor)
                        <tr>
                            <x-da-table-num-td :row="$loop->index" />
                            <td>{{ $floor->name }}</td>
                            <td>{{ $floor->level_number }}</td>
                            <td>{{ $floor->rooms->count() }}</td>
                            <td class="text-end"><a href="{{ route('organization.floors.show', $floor) }}" class="btn btn-sm btn-outline-secondary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No floors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
