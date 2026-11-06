@extends('layouts.app')
@section('page-title', 'Room types')
@section('page-actions')<a href="{{ route('organization.room-types.create') }}" class="btn btn-primary btn-sm">Add type</a>@endsection
@section('content')
    <div class="da-panel">
        <div class="table-responsive da-table-wrap">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <x-da-table-num-th />
                        <th>Name</th>
                        <th>Rooms</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roomTypes as $type)
                        <tr>
                            <x-da-table-num-td :paginator="$roomTypes" :row="$loop->index" />
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->rooms_count }}</td>
                            <td class="text-end"><a href="{{ route('organization.room-types.edit', $type) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-4">No room types.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-da-pagination :paginator="$roomTypes" />
    </div>
@endsection
