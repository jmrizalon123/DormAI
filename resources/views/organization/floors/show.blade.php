@extends('layouts.app')
@section('page-title', $floor->name)
@section('page-subtitle', $floor->building->property->name.' · '.$floor->building->name)
@section('page-actions')
    <a href="{{ route('organization.floors.rooms.create', $floor) }}" class="btn btn-primary btn-sm">Add room</a>
@endsection
@section('content')
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><x-da-table-num-th /><th>Room</th><th>Type</th><th>Beds</th><th>Rate</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($floor->rooms as $room)
                        <tr>
                            <x-da-table-num-td :row="$loop->index" />
                            <td class="fw-medium">{{ $room->room_number }}</td>
                            <td>{{ $room->roomType?->name ?? '—' }}</td>
                            <td>{{ $room->bedSpaces->count() }}/{{ $room->capacity }}</td>
                            <td>₱{{ number_format($room->monthly_rate, 2) }}</td>
                            <td><span class="badge text-bg-{{ $room->status->badgeClass() }}">{{ $room->status->label() }}</span></td>
                            <td class="text-end"><a href="{{ route('organization.rooms.show', $room) }}" class="btn btn-sm btn-outline-secondary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No rooms on this floor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
